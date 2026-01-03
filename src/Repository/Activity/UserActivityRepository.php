<?php

namespace App\Repository\Activity;

use App\Entity\Activity\UserActivity;
use App\Feature\Activity\ActivityRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserActivity>
 */
class UserActivityRepository extends ServiceEntityRepository implements ActivityRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserActivity::class);
    }

    public function add(UserActivity $activity): void
    {
        $this->getEntityManager()->persist($activity);
        $this->getEntityManager()->flush();
    }

    public function addBatch(array $activities): void
    {
        $em = $this->getEntityManager();
        foreach ($activities as $activity) {
            $em->persist($activity);
        }
        $em->flush();
    }

    /**
     * @return UserActivity[]
     */
    public function findByUserPaginated(
        string $userId,
        int $page = 1,
        int $limit = 20,
        ?string $type = null,
        ?\DateTimeImmutable $from = null,
        ?\DateTimeImmutable $to = null
    ): array {
        $qb = $this->createQueryBuilder('a')
            ->andWhere('a.userId = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('a.occurredAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        if ($type !== null) {
            $qb->andWhere('a.type = :type')
                ->setParameter('type', $type);
        }

        if ($from !== null) {
            $qb->andWhere('a.occurredAt >= :from')
                ->setParameter('from', $from);
        }

        if ($to !== null) {
            $qb->andWhere('a.occurredAt <= :to')
                ->setParameter('to', $to);
        }

        return $qb->getQuery()->getResult();
    }

    public function countByUser(
        string $userId,
        ?string $type = null,
        ?\DateTimeImmutable $from = null,
        ?\DateTimeImmutable $to = null
    ): int {
        $qb = $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->andWhere('a.userId = :userId')
            ->setParameter('userId', $userId);

        if ($type !== null) {
            $qb->andWhere('a.type = :type')
                ->setParameter('type', $type);
        }

        if ($from !== null) {
            $qb->andWhere('a.occurredAt >= :from')
                ->setParameter('from', $from);
        }

        if ($to !== null) {
            $qb->andWhere('a.occurredAt <= :to')
                ->setParameter('to', $to);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @return UserActivity[]
     */
    public function findRecentByUser(string $userId, int $limit = 10): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.userId = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('a.occurredAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
