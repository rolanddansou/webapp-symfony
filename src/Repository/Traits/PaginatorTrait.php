<?php

namespace App\Repository\Traits;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Trait pour ajouter la pagination aux repositories.
 * 
 * Évite le memory exhaustion sur hébergement partagé en limitant
 * systématiquement le nombre de résultats chargés.
 * 
 * Utilisation:
 * ```php
 * class UserRepository extends ServiceEntityRepository
 * {
 *     use PaginatorTrait;
 * 
 *     public function findActivePaginated(int $page = 1): Paginator
 *     {
 *         $qb = $this->createQueryBuilder('u')
 *             ->andWhere('u.enabled = true')
 *             ->orderBy('u.createdAt', 'DESC');
 * 
 *         return $this->paginate($qb, $page, limit: 50);
 *     }
 * }
 * ```
 */
trait PaginatorTrait
{
    /**
     * Pagine un QueryBuilder avec gestion automatique des offsets.
     * 
     * @param QueryBuilder $queryBuilder Query à paginer (sans setFirstResult/setMaxResults)
     * @param int $page Numéro de page (commence à 1)
     * @param int $limit Nombre d'items par page (max recommandé: 50 sur hébergement partagé)
     * @param bool $fetchJoinCollection True si JOIN avec collection (OneToMany/ManyToMany)
     * @return Paginator Paginator Doctrine avec résultats et métadonnées
     */
    protected function paginate(
        QueryBuilder $queryBuilder,
        int $page = 1,
        int $limit = 20,
        bool $fetchJoinCollection = false
    ): Paginator {
        // Sécurité: page minimum = 1
        $page = max(1, $page);
        
        // Limite max pour hébergement partagé (évite memory exhaustion)
        $limit = min($limit, 100);
        
        $query = $queryBuilder
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery();
        
        $paginator = new Paginator($query, $fetchJoinCollection);
        
        return $paginator;
    }
    
    /**
     * Retourne les métadonnées de pagination.
     * 
     * @param Paginator $paginator Instance du paginator
     * @param int $page Page actuelle
     * @param int $limit Limite par page
     * @return array<string, mixed> Métadonnées: total, pages, currentPage, hasNext, hasPrev
     */
    protected function getPaginationMetadata(Paginator $paginator, int $page, int $limit): array
    {
        $total = count($paginator);
        $totalPages = (int) ceil($total / $limit);
        
        return [
            'total' => $total,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'perPage' => $limit,
            'hasNext' => $page < $totalPages,
            'hasPrevious' => $page > 1,
            'from' => ($page - 1) * $limit + 1,
            'to' => min($page * $limit, $total),
        ];
    }
    
    /**
     * Retourne un résultat paginé formaté pour API.
     * 
     * @param Paginator $paginator Instance du paginator
     * @param int $page Page actuelle
     * @param int $limit Limite par page
     * @return array<string, mixed> Résultat avec 'data' et 'meta'
     */
    protected function getPaginatedResult(Paginator $paginator, int $page, int $limit): array
    {
        return [
            'data' => iterator_to_array($paginator->getIterator()),
            'meta' => $this->getPaginationMetadata($paginator, $page, $limit),
        ];
    }
}
