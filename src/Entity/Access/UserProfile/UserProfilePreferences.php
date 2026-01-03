<?php

namespace App\Entity\Access\UserProfile;

use App\Entity\Trait\TimestampTrait;
use App\Repository\Access\UserProfile\UserProfilePreferencesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserProfilePreferencesRepository::class)]
#[ORM\HasLifecycleCallbacks]
class UserProfilePreferences
{
    use TimestampTrait;

    #[ORM\Id]
    #[ORM\OneToOne(targetEntity: UserProfile::class, inversedBy: 'preferences')]
    #[ORM\JoinColumn(nullable: false)]
    private ?UserProfile $profile = null;

    #[ORM\Column(length: 10)]
    private string $language = 'fr';

    #[ORM\Column(type: 'boolean')]
    private bool $marketingOptIn = true;

    #[ORM\Column(type: 'boolean')]
    private bool $pushEnabled = true;

    #[ORM\Column(type: 'boolean')]
    private bool $emailEnabled = true;

    public function __construct(UserProfile $customer)
    {
        $this->profile = $customer;
    }

    public function getProfile(): ?UserProfile
    {
        return $this->profile;
    }

    public function setProfile(UserProfile $profile): static
    {
        // set the owning side of the relation if necessary
        if ($profile->getPreferences() !== $this) {
            $profile->setPreferences($this);
        }

        $this->profile = $profile;

        return $this;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function setLanguage(string $language): void
    {
        $this->language = $language;
    }

    public function isMarketingOptIn(): bool
    {
        return $this->marketingOptIn;
    }

    public function setMarketingOptIn(bool $marketingOptIn): void
    {
        $this->marketingOptIn = $marketingOptIn;
    }

    public function isPushEnabled(): bool
    {
        return $this->pushEnabled;
    }

    public function setPushEnabled(bool $pushEnabled): void
    {
        $this->pushEnabled = $pushEnabled;
    }

    public function isEmailEnabled(): bool
    {
        return $this->emailEnabled;
    }

    public function setEmailEnabled(bool $emailEnabled): void
    {
        $this->emailEnabled = $emailEnabled;
    }
}
