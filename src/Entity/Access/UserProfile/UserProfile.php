<?php

namespace App\Entity\Access\UserProfile;

use App\Entity\Access\IdentityInterface;
use App\Entity\Trait\IdTrait;
use App\Entity\Trait\TimestampTrait;
use App\Repository\Access\UserProfile\UserProfileRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserProfileRepository::class)]
#[ORM\HasLifecycleCallbacks]
class UserProfile implements UserProfileInterface
{
    use IdTrait;
    use TimestampTrait;

    const ROLE_USER_PROFILE = 'ROLE_USER_PROFILE';

    #[ORM\OneToOne(targetEntity: IdentityInterface::class)]
    private IdentityInterface $user;

    #[ORM\Column(length: 120)]
    private ?string $fullName = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $phoneNumber = null;

    #[ORM\OneToOne(targetEntity: UserProfilePreferences::class, mappedBy: 'profile', cascade: ["persist"])]
    private UserProfilePreferences|null $preferences = null;

    public function __construct(IdentityInterface $user, string $fullName)
    {
        $this->user = $user;
        $this->fullName = $fullName;
        $this->createdAt = new \DateTimeImmutable();
        $this->preferences= new UserProfilePreferences($this);
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): static
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * @return IdentityInterface
     */
    public function getUser(): IdentityInterface
    {
        return $this->user;
    }

    /**
     * @param IdentityInterface $user
     * @return UserProfile
     */
    public function setUser(IdentityInterface $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getPreferences(): ?UserProfilePreferences
    {
        return $this->preferences;
    }

    public function setPreferences(UserProfilePreferences $preferences): static
    {
        $this->preferences = $preferences;

        return $this;
    }

    public function getCustomerId(): ?string
    {
        if ($this->id) {
            return (string)$this->id;
        }

        return null;
    }

    public function getCustomerFullName(): string
    {
        return $this->fullName ?? '';
    }

    public function getCustomerEmail(): string
    {
        return $this->user->getUserEmail() ?? '';
    }

    public function __toString(): string
    {
        return $this->getCustomerFullName();
    }
}
