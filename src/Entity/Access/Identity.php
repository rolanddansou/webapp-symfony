<?php

namespace App\Entity\Access;

use App\Entity\Trait\EnabledDisabledTrait;
use App\Entity\Trait\IdTrait;
use App\Entity\Trait\TimestampTrait;
use App\Feature\Helper\DateHelper;
use App\Repository\Access\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\HasLifecycleCallbacks]
class Identity implements IdentityInterface
{
    use IdTrait;
    use TimestampTrait;
    use EnabledDisabledTrait;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(type: 'boolean')]
    private bool $emailVerified = false;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $emailVerifiedAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $lastLoginAt = null;

    #[ORM\OneToOne(mappedBy: 'relativeUser', cascade: ['persist', 'remove'])]
    private ?UserCredentials $credentials = null;

    /**
     * @var Collection<int, UserRole>
     */
    #[ORM\ManyToMany(targetEntity: UserRole::class, inversedBy: 'users', cascade: ['persist'])]
    private Collection $roles;

    /**
     * @var Collection<int, UserDevice>
     */
    #[ORM\OneToMany(targetEntity: UserDevice::class, mappedBy: 'relativeUser')]
    private Collection $devices;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
        $this->devices = new ArrayCollection();
    }

    public function isEmailVerified(): bool
    {
        return $this->emailVerified;
    }

    public function setEmailVerified(bool $emailVerified): static
    {
        $this->emailVerified = $emailVerified;
        return $this;
    }

    public function getEmailVerifiedAt(): ?\DateTimeImmutable
    {
        return $this->emailVerifiedAt;
    }

    public function setEmailVerifiedAt(?\DateTimeImmutable $emailVerifiedAt): static
    {
        $this->emailVerifiedAt = $emailVerifiedAt;
        return $this;
    }

    public function markEmailAsVerified(): static
    {
        $this->emailVerified = true;
        $this->emailVerifiedAt = DateHelper::nowUTC();
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getCredentials(): ?UserCredentials
    {
        return $this->credentials;
    }

    public function setCredentials(?UserCredentials $credentials): static
    {
        $this->credentials = $credentials;
        $credentials->setRelativeUser($this);

        return $this;
    }

    /**
     * @return Collection<int, UserRole>
     */
    public function getRoles(): Collection
    {
        return $this->roles;
    }

    public function addRole(UserRole $role): static
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
        }

        return $this;
    }

    public function removeRole(UserRole $role): static
    {
        $this->roles->removeElement($role);

        return $this;
    }

    /**
     * @return Collection<int, UserDevice>
     */
    public function getDevices(): Collection
    {
        return $this->devices;
    }

    public function addDevice(UserDevice $device): static
    {
        if (!$this->devices->contains($device)) {
            $this->devices->add($device);
            $device->setRelativeUser($this);
        }

        return $this;
    }

    public function removeDevice(UserDevice $device): static
    {
        if ($this->devices->removeElement($device)) {
            // set the owning side to null (unless already changed)
            if ($device->getRelativeUser() === $this) {
                $device->setRelativeUser(null);
            }
        }

        return $this;
    }

    public function getUserId(): string
    {
        return (string) $this->getId();
    }

    public function getUserEmail(): string
    {
        if ($this->email === null) {
            return 'user' . $this->getId() . '@fidelys.com';
        }

        return $this->email;
    }

    public function __toString(): string
    {
        return $this->getUserEmail();
    }

    public function setLastLoginAt(\DateTimeImmutable $param)
    {
        $this->lastLoginAt = $param;
        return $this;
    }

    public function getLastLoginAt(): ?\DateTimeImmutable
    {
        return $this->lastLoginAt;
    }
}
