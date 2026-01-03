<?php

namespace App\Feature\Access\Security;

use App\Feature\Access\Service\IdentityUser;
use App\Repository\Access\UserRepository;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @implements UserProviderInterface<IdentityUser>
 */
final readonly class IdentityProvider implements UserProviderInterface
{
    public function __construct(
        private UserRepository $userRepository,
    ) {}

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $identity = $this->userRepository->findByEmail($identifier);

        if (!$identity) {
            throw new UserNotFoundException(sprintf('User "%s" not found.', $identifier));
        }

        return new IdentityUser($identity);
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
