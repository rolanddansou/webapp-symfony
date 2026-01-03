<?php

namespace App\Repository\Traits;

use Symfony\Component\DependencyInjection\Attribute\Required;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Trait pour ajouter des capacités de cache aux repositories.
 * 
 * Utilisation:
 * ```php
 * class UserRepository extends ServiceEntityRepository
 * {
 *     use CacheableRepositoryTrait;
 * 
 *     public function findByEmail(string $email): ?User
 *     {
 *         return $this->cachedQuery(
 *             'user.email.' . md5($email),
 *             fn() => $this->createQueryBuilder('u')
 *                 ->andWhere('u.email = :email')
 *                 ->setParameter('email', $email)
 *                 ->getQuery()
 *                 ->getOneOrNullResult(),
 *             ttl: 300
 *         );
 *     }
 * }
 * ```
 */
trait CacheableRepositoryTrait
{
    private ?CacheInterface $cache = null;
    
    /**
     * Injection automatique du service de cache.
     * Le #[Required] permet l'injection même dans les services étendant ServiceEntityRepository.
     */
    #[Required]
    public function setCache(CacheInterface $cache): void
    {
        $this->cache = $cache;
    }
    
    /**
     * Exécute une query avec mise en cache du résultat.
     * 
     * @param string $key Clé unique de cache (utiliser md5 pour valeurs dynamiques)
     * @param callable $callback Fonction qui exécute la query et retourne le résultat
     * @param int $ttl Durée de vie en cache (secondes). Recommandations:
     *                 - 60-300s : Données changeant fréquemment (utilisateurs connectés)
     *                 - 600-3600s : Données modérément stables (settings, configs)
     *                 - 3600-86400s : Données très stables (rôles, permissions, templates)
     * @return mixed Résultat de la query (depuis cache ou nouvelle exécution)
     */
    protected function cachedQuery(string $key, callable $callback, int $ttl = 3600): mixed
    {
        // Fallback si cache non disponible (dev, tests)
        if (!$this->cache) {
            return $callback();
        }
        
        return $this->cache->get($key, function(ItemInterface $item) use ($callback, $ttl) {
            $item->expiresAfter($ttl);
            return $callback();
        });
    }
    
    /**
     * Invalide une entrée de cache spécifique.
     * À appeler après UPDATE/DELETE pour maintenir cohérence.
     * 
     * @param string $key Clé de cache à invalider
     */
    protected function invalidateCache(string $key): void
    {
        $this->cache?->delete($key);
    }
    
    /**
     * Invalide plusieurs entrées de cache par pattern.
     * Utile pour invalider toutes les queries liées à une entité.
     * 
     * Note: Le cache filesystem ne supporte pas les patterns,
     * cette méthode invalide les clés individuellement.
     * 
     * @param array<string> $keys Liste de clés à invalider
     */
    protected function invalidateCacheMultiple(array $keys): void
    {
        if (!$this->cache) {
            return;
        }
        
        foreach ($keys as $key) {
            $this->cache->delete($key);
        }
    }
    
    /**
     * Génère une clé de cache normalisée pour un tableau de critères.
     * 
     * Exemple: ['status' => 'active', 'role' => 'admin'] 
     *       -> 'prefix.active.admin'
     * 
     * @param string $prefix Préfixe identifiant le type de query
     * @param array<string, mixed> $criteria Critères de recherche
     * @return string Clé de cache générée
     */
    protected function generateCacheKey(string $prefix, array $criteria = []): string
    {
        if (empty($criteria)) {
            return $prefix;
        }
        
        // Trier pour avoir une clé stable
        ksort($criteria);
        
        $parts = [$prefix];
        foreach ($criteria as $value) {
            if (is_object($value)) {
                $parts[] = method_exists($value, 'getId') ? $value->getId() : spl_object_hash($value);
            } elseif (is_array($value)) {
                $parts[] = md5(serialize($value));
            } else {
                $parts[] = (string)$value;
            }
        }
        
        return implode('.', array_map('md5', $parts));
    }
}
