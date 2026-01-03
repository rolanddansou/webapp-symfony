<?php

namespace App\Entity\Access;

use App\Entity\Trait\IdTrait;
use App\Entity\Trait\TimestampTrait;
use App\Repository\Access\EmailVerificationCodeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EmailVerificationCodeRepository::class)]
#[ORM\HasLifecycleCallbacks]
class EmailVerificationCode
{
    use IdTrait;
    use TimestampTrait;

    private const CODE_LENGTH = 6;
    private const EXPIRY_MINUTES = 15;

    #[ORM\ManyToOne(targetEntity: Identity::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Identity $user = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 10)]
    private string $code;

    #[ORM\Column]
    private \DateTimeImmutable $expiresAt;

    #[ORM\Column]
    private bool $used = false;

    #[ORM\Column(length: 30)]
    private string $type = 'email_verification'; // email_verification, password_reset, pre_registration

    public function __construct(?Identity $user = null, string $type = 'email_verification')
    {
        $this->user = $user;
        $this->type = $type;
        $this->code = $this->generateCode();
        $this->expiresAt = new \DateTimeImmutable('+' . self::EXPIRY_MINUTES . ' minutes');
        $this->createdAt = new \DateTimeImmutable();
    }

    /**
     * Create a verification code for an email address (pre-registration, no user yet)
     */
    public static function createForEmail(string $email, string $type = 'pre_registration'): self
    {
        $instance = new self(null, $type);
        $instance->email = strtolower(trim($email));
        return $instance;
    }

    private function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), self::CODE_LENGTH, '0', STR_PAD_LEFT);
    }

    public function getUser(): ?Identity
    {
        return $this->user;
    }

    public function getEmail(): ?string
    {
        // Return email field if set, otherwise get from user
        return $this->email ?? $this->user?->getEmail();
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getExpiresAt(): \DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function isUsed(): bool
    {
        return $this->used;
    }

    public function markAsUsed(): void
    {
        $this->used = true;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isExpired(): bool
    {
        return new \DateTimeImmutable() > $this->expiresAt;
    }

    public function isValid(): bool
    {
        return !$this->used && !$this->isExpired();
    }
}
