<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Response;

/**
 * Service helper pour configurer les headers de cache HTTP.
 * 
 * Permet d'optimiser les performances en activant le cache navigateur
 * et proxy (sans besoin de Varnish sur hébergement partagé).
 * 
 * Usage dans controllers:
 * ```php
 * #[Route('/api/settings')]
 * public function settings(CacheService $cache): Response
 * {
 *     $response = new JsonResponse($data);
 *     return $cache->cachePublic($response, 3600);
 * }
 * ```
 */
class CacheService
{
    /**
     * Configure une réponse pour être mise en cache publiquement.
     * Utilisable par navigateurs et proxies (CDN, reverse proxy).
     * 
     * @param Response $response Réponse à cacher
     * @param int $maxAge Durée en secondes (ex: 3600 = 1h)
     * @param bool $mustRevalidate Forcer validation après expiration
     * @return Response
     */
    public function cachePublic(
        Response $response,
        int $maxAge,
        bool $mustRevalidate = true
    ): Response {
        $response->setPublic();
        $response->setMaxAge($maxAge);
        $response->setSharedMaxAge($maxAge);
        
        if ($mustRevalidate) {
            $response->headers->addCacheControlDirective('must-revalidate', true);
        }
        
        return $response;
    }
    
    /**
     * Configure une réponse pour être mise en cache uniquement dans le navigateur.
     * Pas de cache proxy (données privées/personnalisées).
     * 
     * @param Response $response Réponse à cacher
     * @param int $maxAge Durée en secondes
     * @return Response
     */
    public function cachePrivate(Response $response, int $maxAge): Response
    {
        $response->setPrivate();
        $response->setMaxAge($maxAge);
        
        return $response;
    }
    
    /**
     * Configure une réponse avec ETag pour validation conditionnelle.
     * Le navigateur envoie If-None-Match, serveur répond 304 si inchangé.
     * 
     * @param Response $response Réponse
     * @param string|null $content Contenu pour générer ETag (si null, utilise response content)
     * @param int $maxAge Durée de cache
     * @return Response
     */
    public function cacheWithETag(
        Response $response,
        ?string $content = null,
        int $maxAge = 3600
    ): Response {
        $content = $content ?? $response->getContent();
        $etag = md5($content ?: '');
        
        $response->setETag($etag);
        $response->setPublic();
        $response->setMaxAge($maxAge);
        
        return $response;
    }
    
    /**
     * Configure pour NE PAS mettre en cache.
     * Utilisé pour données sensibles ou très dynamiques.
     * 
     * @param Response $response Réponse
     * @return Response
     */
    public function noCache(Response $response): Response
    {
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        
        return $response;
    }
    
    /**
     * Configure cache pour assets statiques (JS, CSS, images).
     * Cache très long avec immutable (ne revalide jamais).
     * 
     * @param Response $response Réponse
     * @param int $maxAge Durée (défaut: 1 an)
     * @return Response
     */
    public function cacheImmutable(Response $response, int $maxAge = 31536000): Response
    {
        $response->setPublic();
        $response->setMaxAge($maxAge);
        $response->setSharedMaxAge($maxAge);
        $response->headers->addCacheControlDirective('immutable', true);
        
        return $response;
    }
    
    /**
     * Configure cache avec Last-Modified pour validation conditionnelle.
     * 
     * @param Response $response Réponse
     * @param \DateTimeInterface $lastModified Date de dernière modification
     * @param int $maxAge Durée de cache
     * @return Response
     */
    public function cacheWithLastModified(
        Response $response,
        \DateTimeInterface $lastModified,
        int $maxAge = 3600
    ): Response {
        $response->setLastModified($lastModified);
        $response->setPublic();
        $response->setMaxAge($maxAge);
        
        return $response;
    }
}
