<?php

namespace App\Feature\Access\Service;

use App\Entity\Access\LoginAttempt;
use App\Feature\Access\Exception\AuthenticationException;
use App\Repository\Access\LoginAttemptRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;

/**
 * Service for handling rate limiting and account lockout
 */
final class SecurityThrottleService
{
    public function __construct(
        private readonly LoginAttemptRepository $loginAttemptRepository,
        private readonly RequestStack $requestStack,
        #[Target('authLogger')]
        private readonly LoggerInterface $logger,
        private readonly ?RateLimiterFactory $loginLimiter = null,
        private readonly ?RateLimiterFactory $registrationLimiter = null,
        private readonly ?RateLimiterFactory $passwordResetLimiter = null,
    ) {
    }

    /**
     * Check if login is allowed (rate limit + account lockout)
     */
    public function checkLoginAllowed(string $email): void
    {
        $ip = $this->getClientIp();

        // Check account lockout first
        if ($this->loginAttemptRepository->isAccountLocked($email)) {
            $remainingTime = $this->loginAttemptRepository->getRemainingLockoutTime($email);
            $minutes = ceil($remainingTime / 60);

            $this->logger->warning('Account locked due to too many failed attempts', [
                'email' => $email,
                'ip' => $ip,
                'remaining_minutes' => $minutes,
            ]);

            throw new AuthenticationException(
                "Account temporarily locked. Please try again in {$minutes} minute(s).",
                Response::HTTP_TOO_MANY_REQUESTS
            );
        }

        // Check rate limiter
        if ($this->loginLimiter) {
            $limiter = $this->loginLimiter->create($ip);
            $limit = $limiter->consume();

            if (!$limit->isAccepted()) {
                $retryAfter = $limit->getRetryAfter()->getTimestamp() - time();

                $this->logger->warning('Rate limit exceeded for login', [
                    'ip' => $ip,
                    'retry_after' => $retryAfter,
                ]);

                throw new AuthenticationException(
                    "Too many login attempts. Please try again in {$retryAfter} seconds.",
                    Response::HTTP_TOO_MANY_REQUESTS
                );
            }
        }
    }

    /**
     * Record successful login
     */
    public function recordSuccessfulLogin(string $email): void
    {
        $ip = $this->getClientIp();
        $userAgent = $this->getUserAgent();

        // Clear previous failed attempts
        $this->loginAttemptRepository->clearFailedAttempts($email);

        // Record successful attempt
        $this->loginAttemptRepository->recordAttempt(
            $email,
            $ip,
            true,
            $userAgent
        );

        $this->logger->info('Successful login', [
            'email' => $email,
            'ip' => $ip,
        ]);
    }

    /**
     * Record failed login
     */
    public function recordFailedLogin(string $email, string $reason = 'Invalid credentials'): void
    {
        $ip = $this->getClientIp();
        $userAgent = $this->getUserAgent();

        $this->loginAttemptRepository->recordAttempt(
            $email,
            $ip,
            false,
            $userAgent,
            $reason
        );

        $failedCount = $this->loginAttemptRepository->countRecentFailedAttempts(
            $email,
            LoginAttempt::LOCKOUT_DURATION_MINUTES
        );

        $this->logger->warning('Failed login attempt', [
            'email' => $email,
            'ip' => $ip,
            'reason' => $reason,
            'failed_count' => $failedCount,
            'max_attempts' => LoginAttempt::MAX_ATTEMPTS,
        ]);

        // Warn if close to lockout
        if ($failedCount >= LoginAttempt::MAX_ATTEMPTS - 1) {
            $this->logger->warning('Account approaching lockout', [
                'email' => $email,
                'attempts' => $failedCount,
            ]);
        }
    }

    /**
     * Check registration rate limit
     */
    public function checkRegistrationAllowed(): void
    {
        if (!$this->registrationLimiter) {
            return;
        }

        $ip = $this->getClientIp();
        $limiter = $this->registrationLimiter->create($ip);
        $limit = $limiter->consume();

        if (!$limit->isAccepted()) {
            $retryAfter = $limit->getRetryAfter()->getTimestamp() - time();

            throw new AuthenticationException(
                "Too many registration attempts. Please try again later.",
                Response::HTTP_TOO_MANY_REQUESTS
            );
        }
    }

    /**
     * Check password reset rate limit
     */
    public function checkPasswordResetAllowed(string $email): void
    {
        if (!$this->passwordResetLimiter) {
            return;
        }

        $limiter = $this->passwordResetLimiter->create($email);
        $limit = $limiter->consume();

        if (!$limit->isAccepted()) {
            throw new AuthenticationException(
                "Too many password reset requests. Please try again later.",
                Response::HTTP_TOO_MANY_REQUESTS
            );
        }
    }

    /**
     * Get remaining attempts before lockout
     */
    public function getRemainingAttempts(string $email): int
    {
        $failedCount = $this->loginAttemptRepository->countRecentFailedAttempts(
            $email,
            LoginAttempt::LOCKOUT_DURATION_MINUTES
        );

        return max(0, LoginAttempt::MAX_ATTEMPTS - $failedCount);
    }

    private function getClientIp(): string
    {
        $request = $this->requestStack->getCurrentRequest();
        return $request?->getClientIp() ?? '0.0.0.0';
    }

    private function getUserAgent(): ?string
    {
        $request = $this->requestStack->getCurrentRequest();
        return $request?->headers->get('User-Agent');
    }
}
