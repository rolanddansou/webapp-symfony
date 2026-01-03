<?php

namespace App\Feature\Shared\Service;

use Symfony\Component\HttpFoundation\Response;

class HttpCacheService
{
    /**
     * Apply HTTP cache headers to a response
     *
     * @param Response $response The response to apply cache headers to
     * @param int $maxAge Cache max age in seconds (default: 3600 = 1 hour)
     * @param \DateTimeInterface|null $lastModified Last modification date
     * @return Response The response with cache headers applied
     */
    public function applyCacheHeaders(
        Response $response,
        int $maxAge = 3600,
        ?\DateTimeInterface $lastModified = null
    ): Response {
        // Set Cache-Control header
        $response->setPublic();
        $response->setMaxAge($maxAge);

        // Set ETag based on response content
        $etag = md5($response->getContent());
        $response->setEtag($etag);

        // Set Last-Modified if provided
        if ($lastModified !== null) {
            $response->setLastModified($lastModified);
        }

        return $response;
    }

    /**
     * Get the latest modification date from an array of entities
     *
     * @param array $entities Array of entities with getUpdatedAt() method
     * @return \DateTimeInterface|null The latest modification date or null if no entities
     */
    public function getLatestModificationDate(array $entities): ?\DateTimeInterface
    {
        if (empty($entities)) {
            return null;
        }

        $latestDate = null;

        foreach ($entities as $entity) {
            if (method_exists($entity, 'getUpdatedAt')) {
                $updatedAt = $entity->getUpdatedAt();
                if ($updatedAt !== null && ($latestDate === null || $updatedAt > $latestDate)) {
                    $latestDate = $updatedAt;
                }
            }
        }

        return $latestDate;
    }
}
