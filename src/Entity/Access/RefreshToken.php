<?php

namespace App\Entity\Access;

use App\Entity\Trait\TimestampTrait;
use App\Repository\Access\RefreshTokenRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RefreshTokenRepository::class)]
#[ORM\HasLifecycleCallbacks]
class RefreshToken
{
    use TimestampTrait;

    #[ORM\Id]
    #[ORM\Column(length: 200)]
    private string $token;

    #[ORM\ManyToOne(targetEntity: Identity::class)]
    private Identity $user;

    #[ORM\Column]
    private \DateTimeImmutable $expiresAt;

    #[ORM\Column(length: 200)]
    private string $deviceId;

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function getUser(): Identity
    {
        return $this->user;
    }

    public function setUser(Identity $user): void
    {
        $this->user = $user;
    }

    public function getExpiresAt(): \DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(\DateTimeImmutable $expiresAt): void
    {
        $this->expiresAt = $expiresAt;
    }

    public function getDeviceId(): string
    {
        return $this->deviceId;
    }

    public function setDeviceId(string $deviceId): void
    {
        $this->deviceId = $deviceId;
    }
}
