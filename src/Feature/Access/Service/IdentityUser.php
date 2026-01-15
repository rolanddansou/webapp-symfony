<?php

namespace App\Feature\Access\Service;

use App\Entity\Access\AdminUser;
use App\Entity\Access\Identity;
use App\Entity\Access\UserProfile\UserProfile;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Wrapper class for Identity to implement Symfony's UserInterface
 */
final class IdentityUser implements UserInterface, PasswordAuthenticatedUserInterface
{
    public function __construct(
        private Identity $identity,
    ) {}

    /**
     * @var UserProfile|null
     */
    private ?UserProfile $profileCacheUserProfile = null;

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

    public function getId(): ?Uuid
    {
        return $this->identity->getId();
    }

    public function isUserProfile(EntityManagerInterface $entityManager): bool
    {
        if($this->profileCacheUserProfile !== null) {
            return true;
        }

        $userProfile = $entityManager->getRepository(UserProfile::class)->findOneBy([
            "user" => $this->getId()
        ]);

        $this->profileCacheUserProfile = $userProfile;

        return $userProfile !== null;
    }

    public function getProfile(EntityManagerInterface $entityManager): ?UserProfile
    {
        if($this->profileCacheUserProfile !== null) {
            return $this->profileCacheUserProfile;
        }

        $userProfile = $entityManager->getRepository(UserProfile::class)->findOneBy([
            "user" => $this->getId()
        ]);

        $this->profileCacheUserProfile = $userProfile;

        return $userProfile;
    }

    public function isAdminUser(EntityManagerInterface $manager): bool
    {
        $adminUser= $manager->getRepository(AdminUser::class)->findOneBy([
            "user" => $this->getId()
        ]);

        return $adminUser !== null;
    }
}
