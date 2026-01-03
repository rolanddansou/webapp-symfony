<?php

namespace App\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Monitore les performances de chaque requête HTTP.
 * 
 * Logue automatiquement les requêtes lentes, memory leaks,
 * et queries excessives pour identifier les bottlenecks
 * en production sur hébergement partagé.
 * 
 * Configuré dans services.yaml avec tag 'kernel.event_subscriber'
 */
class PerformanceSubscriber implements EventSubscriberInterface
{
    private float $startTime;
    private int $startMemory;
    private ?int $queryCount = null;
    
    public function __construct(
        private readonly LoggerInterface $performanceLogger,
        private readonly EntityManagerInterface $entityManager,
        private readonly bool $debug = false
    ) {}
    
    /**
     * Événements écoutés avec priorités.
     * RequestEvent à priorité haute (1024) pour capturer début dès que possible.
     * ResponseEvent à priorité basse (-1024) pour mesurer jusqu'à la fin.
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 1024],
            KernelEvents::RESPONSE => ['onKernelResponse', -1024],
        ];
    }
    
    /**
     * Capturé au début de chaque requête.
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        // Ignorer les sub-requests (ESI, forwards, etc.)
        if (!$event->isMainRequest()) {
            return;
        }
        
        $this->startTime = microtime(true);
        $this->startMemory = memory_get_usage(true);
        
        // Compter les queries Doctrine (si disponible)
        try {
            $config = $this->entityManager->getConnection()->getConfiguration();
            if ($config && method_exists($config, 'getSQLLogger')) {
                $logger = $config->getSQLLogger();
                if ($logger && method_exists($logger, 'queries')) {
                    $this->queryCount = count($logger->queries ?? []);
                }
            }
        } catch (\Exception $e) {
            // Silently fail si SQLLogger non disponible
        }
    }
    
    /**
     * Capturé à la fin de chaque requête.
     * Calcule et logue les métriques si seuils dépassés.
     */
    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }
        
        $request = $event->getRequest();
        $response = $event->getResponse();
        
        // Calcul des métriques
        $duration = microtime(true) - $this->startTime;
        $memoryPeak = memory_get_peak_usage(true);
        $memoryUsed = $memoryPeak - $this->startMemory;
        $memoryPeakMB = round($memoryPeak / 1024 / 1024, 2);
        $memoryUsedMB = round($memoryUsed / 1024 / 1024, 2);
        
        // Compter queries finales
        $queries = 0;
        try {
            $config = $this->entityManager->getConnection()->getConfiguration();
            if ($config && method_exists($config, 'getSQLLogger')) {
                $logger = $config->getSQLLogger();
                if ($logger && method_exists($logger, 'queries')) {
                    $currentCount = count($logger->queries ?? []);
                    $queries = $this->queryCount !== null ? $currentCount - $this->queryCount : $currentCount;
                }
            }
        } catch (\Exception $e) {
            // Silently fail
        }
        
        // Contexte de log
        $context = [
            'method' => $request->getMethod(),
            'uri' => $request->getRequestUri(),
            'route' => $request->attributes->get('_route'),
            'status' => $response->getStatusCode(),
            'duration_ms' => round($duration * 1000, 2),
            'memory_peak_mb' => $memoryPeakMB,
            'memory_used_mb' => $memoryUsedMB,
            'queries' => $queries,
        ];
        
        // Seuils d'alerte pour hébergement partagé
        $isSlowRequest = $duration > 1.0;  // > 1 seconde
        $isMemoryIntensive = $memoryPeakMB > 100;  // > 100MB
        $hasExcessiveQueries = $queries > 20;  // > 20 queries
        
        // Logging selon sévérité
        if ($isSlowRequest || $isMemoryIntensive || $hasExcessiveQueries) {
            $warnings = [];
            
            if ($isSlowRequest) {
                $warnings[] = sprintf('SLOW REQUEST (%.2fs)', $duration);
            }
            if ($isMemoryIntensive) {
                $warnings[] = sprintf('HIGH MEMORY (%sMB)', $memoryPeakMB);
            }
            if ($hasExcessiveQueries) {
                $warnings[] = sprintf('EXCESSIVE QUERIES (%d)', $queries);
            }
            
            $this->performanceLogger->warning(
                'Performance issue: ' . implode(' | ', $warnings),
                $context
            );
        } elseif ($this->debug) {
            // En dev, log toutes les requêtes pour profiling
            $this->performanceLogger->info(
                'Request completed',
                $context
            );
        }
        
        // Ajouter header de monitoring (optionnel, visible dans DevTools)
        if ($this->debug) {
            $response->headers->set('X-Debug-Duration', (string)round($duration * 1000, 2) . 'ms');
            $response->headers->set('X-Debug-Memory', $memoryPeakMB . 'MB');
            $response->headers->set('X-Debug-Queries', (string)$queries);
        }
    }
}
