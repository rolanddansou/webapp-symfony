<?php

namespace App\Entity\Access;

use App\Entity\Trait\IdTrait;
use App\Entity\Trait\TimestampTrait;
use App\Repository\Access\LoginAttemptRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Tracks failed login attempts for account lockout
 */
#[ORM\Entity(repositoryClass: LoginAttemptRepository::class)]
#[ORM\Table(name: 'login_attempts')]
#[ORM\Index(columns: ['email'], name: 'idx_login_attempt_email')]
#[ORM\Index(columns: ['ip_address'], name: 'idx_login_attempt_ip')]
#[ORM\HasLifecycleCallbacks]
class LoginAttempt
{
    use IdTrait;
    use TimestampTrait;

    public const MAX_ATTEMPTS = 5;
    public const LOCKOUT_DURATION_MINUTES = 15;

    #[ORM\Column(length: 200)]
    private string $email;

    #[ORM\Column(length: 45)]
    private string $ipAddress;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $userAgent = null;

    #[ORM\Column(type: 'boolean')]
    private bool $successful = false;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $failureReason = null;

    public function __construct(string $email, string $ipAddress)
    {
        $this->email = strtolower($email);
        $this->ipAddress = $ipAddress;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getIpAddress(): string
    {
        return $this->ipAddress;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent): static
    {
        $this->userAgent = $userAgent;
        return $this;
    }

    public function isSuccessful(): bool
    {
        return $this->successful;
    }

    public function markSuccessful(): static
    {
        $this->successful = true;
        return $this;
    }

    public function getFailureReason(): ?string
    {
        return $this->failureReason;
    }

    public function setFailureReason(?string $failureReason): static
    {
        $this->failureReason = $failureReason;
        return $this;
    }
}
