<?php

namespace App\Entity\System;

use App\Feature\Helper\DateHelper;
use App\Repository\System\SystemSettingRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SystemSettingRepository::class)]
#[ORM\Table(name: 'system_setting')]
#[ORM\HasLifecycleCallbacks]
class SystemSetting
{
    #[ORM\Id]
    #[ORM\Column(length: 80)]
    private string $key;

    #[ORM\Column(type: "json")]
    private array $value;

    #[ORM\Column(length: 120)]
    private string $description;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function __construct(string $key, array $value, string $description)
    {
        $this->key = $key;
        $this->value = $value;
        $this->description = $description;
        $this->updatedAt = DateHelper::nowUTC();
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): void
    {
        $this->key = $key;
    }

    public function getValue(): array
    {
        return $this->value;
    }

    public function setValue(array $value): void
    {
        $this->value = $value;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->updatedAt = DateHelper::nowUTC();
    }
}
