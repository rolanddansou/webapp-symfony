<?php

namespace App\Repository\Access;

use App\Entity\Access\LoginAttempt;
use App\Feature\Helper\DateHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LoginAttempt>
 */
class LoginAttemptRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LoginAttempt::class);
    }

    /**
     * Count failed attempts in the last N minutes for an email
     */
    public function countRecentFailedAttempts(string $email, int $minutes = 15): int
    {
        $since = new \DateTimeImmutable("-{$minutes} minutes");

        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('LOWER(a.email) = :email')
            ->andWhere('a.successful = :successful')
            ->andWhere('a.createdAt >= :since')
            ->setParameter('email', strtolower($email))
            ->setParameter('successful', false)
            ->setParameter('since', $since)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Count failed attempts in the last N minutes from an IP
     */
    public function countRecentFailedAttemptsFromIp(string $ip, int $minutes = 15): int
    {
        $since = new \DateTimeImmutable("-{$minutes} minutes");

        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.ipAddress = :ip')
            ->andWhere('a.successful = :successful')
            ->andWhere('a.createdAt >= :since')
            ->setParameter('ip', $ip)
            ->setParameter('successful', false)
            ->setParameter('since', $since)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Check if account is locked
     */
    public function isAccountLocked(string $email): bool
    {
        return $this->countRecentFailedAttempts(
            $email,
            LoginAttempt::LOCKOUT_DURATION_MINUTES
        ) >= LoginAttempt::MAX_ATTEMPTS;
    }

    /**
     * Get remaining lockout time in seconds
     */
    public function getRemainingLockoutTime(string $email): int
    {
        $since = new \DateTimeImmutable('-' . LoginAttempt::LOCKOUT_DURATION_MINUTES . ' minutes');

        /** @var LoginAttempt|null $lastAttempt */
        $lastAttempt = $this->createQueryBuilder('a')
            ->where('LOWER(a.email) = :email')
            ->andWhere('a.successful = :successful')
            ->andWhere('a.createdAt >= :since')
            ->setParameter('email', strtolower($email))
            ->setParameter('successful', false)
            ->setParameter('since', $since)
            ->orderBy('a.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$lastAttempt) {
            return 0;
        }

        $lockoutEnds = $lastAttempt->getCreatedAt()->modify('+' . LoginAttempt::LOCKOUT_DURATION_MINUTES . ' minutes');
        $now = DateHelper::nowUTC();

        if ($lockoutEnds <= $now) {
            return 0;
        }

        return $lockoutEnds->getTimestamp() - $now->getTimestamp();
    }

    /**
     * Clear failed attempts after successful login
     */
    public function clearFailedAttempts(string $email): void
    {
        $this->createQueryBuilder('a')
            ->delete()
            ->where('LOWER(a.email) = :email')
            ->andWhere('a.successful = :successful')
            ->setParameter('email', strtolower($email))
            ->setParameter('successful', false)
            ->getQuery()
            ->execute();
    }

    /**
     * Record a login attempt
     */
    public function recordAttempt(
        string $email,
        string $ipAddress,
        bool $successful,
        ?string $userAgent = null,
        ?string $failureReason = null
    ): LoginAttempt {
        $attempt = new LoginAttempt($email, $ipAddress);
        $attempt->setUserAgent($userAgent);

        if ($successful) {
            $attempt->markSuccessful();
        } else {
            $attempt->setFailureReason($failureReason);
        }

        $this->getEntityManager()->persist($attempt);
        $this->getEntityManager()->flush();

        return $attempt;
    }

    /**
     * Cleanup old attempts (for scheduled task)
     */
    public function cleanupOldAttempts(int $daysToKeep = 30): int
    {
        $before = new \DateTimeImmutable("-{$daysToKeep} days");

        return $this->createQueryBuilder('a')
            ->delete()
            ->where('a.createdAt < :before')
            ->setParameter('before', $before)
            ->getQuery()
            ->execute();
    }
}
