<?php

namespace App\Repository\Access;

use App\Entity\Access\UserDevice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<UserDevice>
 */
class UserDeviceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserDevice::class);
    }

    /**
     * Find all devices for a user with push token.
     * @return UserDevice[]
     */
    public function findActiveByUserId(string $userId): array
    {
        return $this->createQueryBuilder('d')
            ->join('d.relativeUser', 'u')
            ->andWhere('u.id = :userId')
            ->andWhere('d.pushToken IS NOT NULL and d.isEnabled = true')
            ->setParameter('userId', Uuid::fromString($userId), 'uuid')
            ->orderBy('d.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find device by push token.
     */
    public function findByPushToken(string $token): ?UserDevice
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.pushToken = :token')
            ->setParameter('token', $token)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find device by device ID.
     */
    public function findByDeviceId(string $deviceId): ?UserDevice
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.deviceId = :deviceId')
            ->setParameter('deviceId', $deviceId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Remove all devices for a user.
     */
    public function removeAllForUser(string $userId): int
    {
        return $this->createQueryBuilder('d')
            ->delete()
            ->where('d.relativeUser = :userId')
            ->setParameter('userId', Uuid::fromString($userId), 'uuid')
            ->getQuery()
            ->execute();
    }

    public function save(UserDevice $device, bool $flush = false): void
    {
        $this->getEntityManager()->persist($device);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
