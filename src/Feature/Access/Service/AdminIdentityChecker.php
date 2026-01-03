<?php

namespace App\Feature\Access\Service;

use App\Feature\Shared\Domain\IRoleManager;
use App\Feature\Helper\DateHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

readonly class AdminIdentityChecker implements UserCheckerInterface
{
    public function __construct(
        private EntityManagerInterface $manager,
        private IRoleManager $roleManager,
    ) {
    }

    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof IdentityUser) {
            return;
        }

        $identity = $user->getIdentity();

        if (!$identity->isEnabled()) {
            throw new CustomUserMessageAccountStatusException("Votre compte n'est pas actif.");
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user instanceof IdentityUser) {
            return;
        }

        $identity = $user->getIdentity();

        $identity->setLastLoginAt(DateHelper::nowUTC());
        $this->manager->persist($identity);
        $this->manager->flush();
    }
}
