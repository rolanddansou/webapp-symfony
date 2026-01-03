<?php

namespace App\Repository\Access;

use App\Entity\Access\UserRole;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserRole>
 */
class UserRoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserRole::class);
    }

    public function getOrCreateRole(string $roleName, string|null $name = null): UserRole
    {
        $role = $this->findOneBy(['code' => $roleName]);

        if (!$role) {
            $role = new UserRole();
            $role->setCode($roleName);
            $role->setName($name ?? $roleName);
            $this->getEntityManager()->persist($role);
            $this->getEntityManager()->flush();
        }

        return $role;
    }
}
