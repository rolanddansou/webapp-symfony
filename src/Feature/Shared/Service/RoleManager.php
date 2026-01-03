<?php

namespace App\Feature\Shared\Service;

use App\Entity\Access\AdminUser;
use App\Entity\Access\UserProfile\UserProfile;
use App\Feature\Shared\Domain\IRoleManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class RoleManager implements IRoleManager
{
    private array $cache=[];

    public function __construct(private Security $security, private EntityManagerInterface $manager){}

    public function isConnected(): bool
    {
        return $this->security->getUser()!==null;
    }

    public function isAdmin(?UserInterface $user = null): bool
    {
        return $this->security->isGranted(AdminUser::ROLE_ADMIN, $user);
    }

    public function getUser(): UserInterface
    {
        if($this->security->getUser() instanceof UserInterface){
            return $this->security->getUser();
        }

        throw new \Exception('User is not connected');
    }


    public function hasRole(string $role): bool
    {
        return $this->security->isGranted($role);
    }

    public function isBackendUser(UserInterface|null $user = null): bool
    {
        return $this->security->isGranted("ROLE_BACKEND", $user);
    }

    public function isFrontEndUser(UserInterface|null $user = null): bool
    {
        return $this->security->isGranted(UserProfile::ROLE_USER_PROFILE, $user);
    }
}
