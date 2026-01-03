<?php

namespace App\Repository\Notification;

use App\Entity\Notification\UserNotificationPreference;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserNotificationPreference>
 */
class UserNotificationPreferenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserNotificationPreference::class);
    }

    public function findOneByUserId(string $userId): ?UserNotificationPreference
    {
        return $this->createQueryBuilder('p')
            ->join('p.user', 'u')
            ->andWhere('u.id = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOrCreateForUserId(string $userId, callable $userFinder): UserNotificationPreference
    {
        $preference = $this->findOneByUserId($userId);
        
        if ($preference === null) {
            $user = $userFinder($userId);
            if ($user === null) {
                throw new \RuntimeException('User not found');
            }
            
            $preference = new UserNotificationPreference();
            $preference->setUser($user);
            
            $this->getEntityManager()->persist($preference);
            $this->getEntityManager()->flush();
        }
        
        return $preference;
    }
}
