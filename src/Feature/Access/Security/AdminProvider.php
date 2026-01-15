<?php

namespace App\Feature\Access\Security;

use App\Feature\Access\Service\IdentityUser;
use App\Repository\Access\AdminUserRepository;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @implements UserProviderInterface<IdentityUser>
 */
final readonly class AdminProvider implements UserProviderInterface
{
    public function __construct(
        private AdminUserRepository $adminUserRepository,
    ) {
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        // On cherche un AdminUser qui est lié à une Identity avec cet email
        $adminUser = $this->adminUserRepository->createQueryBuilder('a')
            ->join('a.user', 'u')
            ->where('u.email = :email')
            ->setParameter('email', $identifier)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$adminUser) {
            throw new UserNotFoundException(sprintf('Admin user "%s" not found.', $identifier));
        }

        return new IdentityUser($adminUser->getUser());
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof IdentityUser) {
            throw new \InvalidArgumentException('Invalid user class.');
        }

        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return $class === IdentityUser::class || is_subclass_of($class, IdentityUser::class);
    }
}
