<?php

namespace App\Entity\Access;

use App\Entity\Trait\EnabledDisabledTrait;
use App\Entity\Trait\IdTrait;
use App\Entity\Trait\TimestampTrait;
use App\EntityListener\UserCredentialsListener;
use App\Repository\Access\UserCredentialsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserCredentialsRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\EntityListeners([UserCredentialsListener::class])]
class UserCredentials
{
    use IdTrait;
    use TimestampTrait;
    use EnabledDisabledTrait;

    #[ORM\OneToOne(inversedBy: 'credentials')]
    private ?Identity $relativeUser = null;

    #[ORM\Column(nullable: true)]
    private ?string $passwordHash = null;

    private ?string $plainPassword = null;

    #[ORM\Column(type: 'json')]
    private array $oauthProviders = []; // Google, Facebook, etc.

    #[ORM\Column(type: 'boolean')]
    private bool $twoFactorEnabled = false;

    public function getRelativeUser(): ?Identity
    {
        return $this->relativeUser;
    }

    public function setRelativeUser(?Identity $relativeUser): static
    {
        // unset the owning side of the relation if necessary
        if ($relativeUser === null && $this->relativeUser !== null) {
            $this->relativeUser->setCredentials(null);
        }

        // set the owning side of the relation if necessary
        if ($relativeUser !== null && $relativeUser->getCredentials() !== $this) {
            $relativeUser->setCredentials($this);
        }

        $this->relativeUser = $relativeUser;

        return $this;
    }

    public function getPasswordHash(): ?string
    {
        return $this->passwordHash;
    }

    public function setPasswordHash(?string $passwordHash): void
    {
        $this->passwordHash = $passwordHash;
    }

    public function getOauthProviders(): array
    {
        return $this->oauthProviders;
    }

    public function setOauthProviders(array $oauthProviders): void
    {
        $this->oauthProviders = $oauthProviders;
    }

    public function isTwoFactorEnabled(): bool
    {
        return $this->twoFactorEnabled;
    }

    public function setTwoFactorEnabled(bool $twoFactorEnabled): void
    {
        $this->twoFactorEnabled = $twoFactorEnabled;
    }

    /**
     * @return string|null
     */
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    /**
     * @param string|null $plainPassword
     */
    public function setPlainPassword(?string $plainPassword): void
    {
        $this->plainPassword = $plainPassword;
        $this->updatedAt = \App\Feature\Helper\DateHelper::nowUTC();
    }

    public function __toString(): string
    {
        return $this->relativeUser ? $this->relativeUser->getUserEmail() : 'No User';
    }
}
