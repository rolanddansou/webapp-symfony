<?php

namespace App\Entity\System;

use App\Entity\Trait\IdTrait;
use App\Feature\Helper\DateHelper;
use App\Repository\System\ApiKeyRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ApiKeyRepository::class)]
#[ORM\Table(name: 'system_api_key')]
class ApiKey
{
    use IdTrait;

    #[ORM\Column(length: 100, unique: true)]
    private string $key;

    #[ORM\Column(length: 100)]
    private string $name;

    #[ORM\Column(type: "json")]
    private array $permissions = [];

    #[ORM\Column]
    private bool $isActive = true;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct(string $name, array $permissions)
    {
        $this->key = bin2hex(random_bytes(32));
        $this->name = $name;
        $this->permissions = $permissions;
        $this->createdAt = DateHelper::nowUTC();
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): void
    {
        $this->key = $key;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getPermissions(): array
    {
        return $this->permissions;
    }

    public function setPermissions(array $permissions): void
    {
        $this->permissions = $permissions;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}
