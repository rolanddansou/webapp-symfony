<?php

namespace App\Feature\Shared\Domain;

use Symfony\Component\Security\Core\User\UserInterface;

interface IRoleManager
{
    /**
     * @return UserInterface
     * @throws \Exception
     */
    public function getUser(): UserInterface;
    public function isConnected(): bool;
    public function hasRole(string $role): bool;
    public function isAdmin(?UserInterface $user = null): bool;
    public function isBackendUser(?UserInterface $user = null): bool;
    public function isFrontEndUser(?UserInterface $user = null): bool;
}
