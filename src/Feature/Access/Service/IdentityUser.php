<?php

namespace App\Feature\Access\Service;

use App\Entity\Access\Identity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Wrapper class for Identity to implement Symfony's UserInterface
 */
final readonly class IdentityUser implements UserInterface, PasswordAuthenticatedUserInterface
{
    public function __construct(
        private Identity $identity,
    ) {}

    public function getIdentity(): Identity
    {
        return $this->identity;
    }

    public function getRoles(): array
    {
        $roles = ['ROLE_USER'];

        foreach ($this->identity->getRoles() as $role) {
            $roles[] = $role->getCode(); // Use code instead of name for Symfony roles
        }

        return array_unique($roles);
    }

    public function eraseCredentials(): void
    {
        // Nothing to erase
    }

    public function getUserIdentifier(): string
    {
        return $this->identity->getUserEmail();
    }

    public function getPassword(): ?string
    {
        return $this->identity->getCredentials()?->getPasswordHash();
    }
}
