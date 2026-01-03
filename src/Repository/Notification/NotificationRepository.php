<?php

namespace App\Repository\Notification;

use App\Entity\Access\IdentityInterface;
use App\Entity\Notification\Notification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<Notification>
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    public function findUnreadByUser(IdentityInterface $user, int $limit = 50): array
    {
        return $this->createQueryBuilder('n')
            ->where('n.user = :user')
            ->andWhere('n.readAt IS NULL')
            ->setParameter('user', Uuid::fromString($user->getUserId()), 'uuid')
            ->orderBy('n.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findRecentByUser(IdentityInterface $user, int $limit = 50, ?string $type = null, bool $unreadOnly = false): array
    {
        $qb = $this->createQueryBuilder('n')
            ->where('n.user = :user')
            ->setParameter('user', Uuid::fromString($user->getUserId()), 'uuid')
            ->orderBy('n.createdAt', 'DESC')
            ->setMaxResults($limit);

        if ($type) {
            $qb->andWhere('n.type like :type')
                ->setParameter('type', "%{$type}%");
        }

        if ($unreadOnly) {
            $qb->andWhere('n.readAt IS NULL');
        }

        return $qb->getQuery()->getResult();
    }

    public function countUnreadByUser(IdentityInterface $user): int
    {
        return (int) $this->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->where('n.user = :user')
            ->andWhere('n.readAt IS NULL')
            ->setParameter('user', Uuid::fromString($user->getUserId()), 'uuid')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function markAllAsReadForUser(IdentityInterface $user): int
    {
        return $this->createQueryBuilder('n')
            ->update()
            ->set('n.readAt', ':now')
            ->where('n.user = :user')
            ->andWhere('n.readAt IS NULL')
            ->setParameter('user', Uuid::fromString($user->getUserId()), 'uuid')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->execute();
    }

    public function deleteOldNotifications(int $daysOld = 90): int
    {
        $date = new \DateTime();
        $date->modify("-{$daysOld} days");

        return $this->createQueryBuilder('n')
            ->delete()
            ->where('n.createdAt < :date')
            ->setParameter('date', $date)
            ->getQuery()
            ->execute();
    }
}
