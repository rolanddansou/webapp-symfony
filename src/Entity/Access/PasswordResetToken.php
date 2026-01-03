<?php

namespace App\Entity\Access;

use App\Entity\Trait\TimestampTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class PasswordResetToken
{
    use TimestampTrait;

    #[ORM\Id]
    #[ORM\Column(length: 100)]
    private string $token;

    #[ORM\ManyToOne(targetEntity: Identity::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Identity $user;

    #[ORM\Column]
    private \DateTimeImmutable $expiresAt;

    #[ORM\Column]
    private bool $used = false;

    public function __construct(Identity $user)
    {
        $this->token = bin2hex(random_bytes(32));
        $this->user = $user;
        $this->expiresAt = new \DateTimeImmutable('+1 hour');
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getUser(): Identity
    {
        return $this->user;
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

    public function isValid(): bool
    {
        return !$this->used && $this->expiresAt > new \DateTimeImmutable();
    }
}
