<?php

namespace App\Repository\Access;

use App\Entity\Access\EmailVerificationCode;
use App\Entity\Access\Identity;
use App\Feature\Helper\DateHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EmailVerificationCode>
 */
class EmailVerificationCodeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmailVerificationCode::class);
    }

    public function save(EmailVerificationCode $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(EmailVerificationCode $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findValidCode(Identity $user, string $code, string $type): ?EmailVerificationCode
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.user = :user')
            ->andWhere('e.code = :code')
            ->andWhere('e.type = :type')
            ->andWhere('e.used = false')
            ->andWhere('e.expiresAt > :now')
            ->setParameter('user', $user)
            ->setParameter('code', $code)
            ->setParameter('type', $type)
            ->setParameter('now', DateHelper::nowUTC())
            ->orderBy('e.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findLatestByUserAndType(Identity $user, string $type): ?EmailVerificationCode
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.user = :user')
            ->andWhere('e.type = :type')
            ->setParameter('user', $user)
            ->setParameter('type', $type)
            ->orderBy('e.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function invalidateAllForUser(Identity $user, string $type): void
    {
        $this->createQueryBuilder('e')
            ->update()
            ->set('e.used', 'true')
            ->andWhere('e.user = :user')
            ->andWhere('e.type = :type')
            ->andWhere('e.used = false')
            ->setParameter('user', $user)
            ->setParameter('type', $type)
            ->getQuery()
            ->execute();
    }

    public function deleteExpired(): int
    {
        return $this->createQueryBuilder('e')
            ->delete()
            ->andWhere('e.expiresAt < :now')
            ->setParameter('now', DateHelper::nowUTC())
            ->getQuery()
            ->execute();
    }

    /**
     * Find a valid verification code by email address (for pre-registration codes)
     */
    public function findValidCodeByEmail(string $email, string $code, string $type): ?EmailVerificationCode
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.email = :email')
            ->andWhere('e.code = :code')
            ->andWhere('e.type = :type')
            ->andWhere('e.used = false')
            ->andWhere('e.expiresAt > :now')
            ->setParameter('email', strtolower(trim($email)))
            ->setParameter('code', $code)
            ->setParameter('type', $type)
            ->setParameter('now', DateHelper::nowUTC())
            ->orderBy('e.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find the latest verification code by email and type
     */
    public function findLatestByEmailAndType(string $email, string $type): ?EmailVerificationCode
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.email = :email')
            ->andWhere('e.type = :type')
            ->setParameter('email', strtolower(trim($email)))
            ->setParameter('type', $type)
            ->orderBy('e.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Invalidate all codes for an email address
     */
    public function invalidateAllForEmail(string $email, string $type): void
    {
        $this->createQueryBuilder('e')
            ->update()
            ->set('e.used', 'true')
            ->andWhere('e.email = :email')
            ->andWhere('e.type = :type')
            ->andWhere('e.used = false')
            ->setParameter('email', strtolower(trim($email)))
            ->setParameter('type', $type)
            ->getQuery()
            ->execute();
    }
}

