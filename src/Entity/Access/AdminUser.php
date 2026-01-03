<?php

namespace App\Entity\Access;

use App\Entity\Trait\IdTrait;
use App\Entity\Trait\TimestampTrait;
use App\Repository\Access\AdminUserRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AdminUserRepository::class)]
#[ORM\HasLifecycleCallbacks]
class AdminUser
{
    public const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';
    public const ROLE_ADMIN = 'ROLE_ADMIN';

    use IdTrait;
    use TimestampTrait;

    #[ORM\OneToOne(targetEntity: IdentityInterface::class)]
    #[ORM\JoinColumn(nullable: false)]
    private IdentityInterface $user;

    #[ORM\Column(length: 120)]
    private string $fullName;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $title = null; // Ex: "System Administrator", "Support Manager"

    public function __construct(IdentityInterface $user, string $fullName)
    {
        $this->user = $user;
        $this->fullName = $fullName;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getUser(): IdentityInterface
    {
        return $this->user;
    }

    public function setUser(IdentityInterface $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): static
    {
        $this->fullName = $fullName;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;
        return $this;
    }
}
