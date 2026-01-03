<?php

namespace App\Entity\Access;

use App\Entity\Trait\TimestampTrait;
use App\Repository\Access\PermissionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PermissionRepository::class)]
#[ORM\Table(name: 'permissions')]
#[ORM\UniqueConstraint(name: 'permission_code_unique', columns: ['code'])]
#[ORM\HasLifecycleCallbacks]
class Permission
{
    use TimestampTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    private string $code;

    #[ORM\Column(length: 150)]
    private string $name;

    #[ORM\Column(length: 50)]
    private string $module;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $isActive = true;

    public function __construct(string $code, string $name, string $module)
    {
        $this->code = $code;
        $this->name = $name;
        $this->module = $module;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getModule(): string
    {
        return $this->module;
    }

    public function setModule(string $module): static
    {
        $this->module = $module;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
        return $this;
    }

    /**
     * Check if permission matches a pattern (e.g., 'loyalty:*' matches 'loyalty:points:add')
     */
    public function matches(string $pattern): bool
    {
        if ($pattern === '*') {
            return true;
        }

        if (str_ends_with($pattern, ':*')) {
            $prefix = substr($pattern, 0, -1);
            return str_starts_with($this->code, $prefix);
        }

        return $this->code === $pattern;
    }
}
