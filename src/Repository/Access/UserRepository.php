<?php

namespace App\Repository\Access;

use App\Entity\Access\Identity;
use App\Repository\Traits\CacheableRepositoryTrait;
use App\Repository\Traits\PaginatorTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Identity>
 */
class UserRepository extends ServiceEntityRepository
{
    use CacheableRepositoryTrait;
    use PaginatorTrait;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Identity::class);
    }

    public function save(Identity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
            
            // Invalider le cache pour cet utilisateur
            if ($entity->getEmail()) {
                $this->invalidateCache('user.email.' . md5($entity->getEmail()));
            }
        }
    }

    public function remove(Identity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByEmail(string $email): ?Identity
    {
        return $this->cachedQuery(
            'user.email.' . md5($email),
            fn() => $this->createQueryBuilder('u')
                ->andWhere('u.email = :email')
                ->setParameter('email', $email)
                ->getQuery()
                ->getOneOrNullResult(),
            ttl: 300  // 5 minutes (données authentification)
        );
    }
    
    /**
     * Trouve les utilisateurs actifs avec pagination.
     * Recommandé pour listes et exports sur hébergement partagé.
     */
    public function findActivePaginated(int $page = 1, int $limit = 20): Paginator
    {
        $qb = $this->createQueryBuilder('u')
            ->andWhere('u.enabled = true')
            ->orderBy('u.createdAt', 'DESC');
        
        return $this->paginate($qb, $page, $limit);
    }
    
    /**
     * Compte les utilisateurs avec cache (évite COUNT(*) répété).
     */
    public function countTotal(): int
    {
        return $this->cachedQuery(
            'user.count.total',
            fn() => (int) $this->createQueryBuilder('u')
                ->select('COUNT(u.id)')
                ->getQuery()
                ->getSingleScalarResult(),
            ttl: 1800  // 30 minutes
        );
    }
}
