<?php

namespace App\EntityListener;

use App\Entity\Access\UserCredentials;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;

readonly class UserCredentialsListener
{
    public function __construct(
        private EntityManagerInterface $manager
    ) {}

    public function prePersist(UserCredentials $credential): void
    {
        $this->handlePasswordHashing($credential);
    }

    public function preUpdate(UserCredentials $credential): void
    {
        if ($this->handlePasswordHashing($credential)) {
            $meta = $this->manager->getClassMetadata(UserCredentials::class);
            $this->manager->getUnitOfWork()->recomputeSingleEntityChangeSet($meta, $credential);
        }
    }

    /**
     * Hash le mot de passe si un plainPassword est défini.
     * Retourne true si un hash a été appliqué.
     */
    private function handlePasswordHashing(UserCredentials $credential): bool
    {
        $plainPassword = $credential->getPlainPassword();

        if (!$plainPassword) {
            return false;
        }

        // configure different hashers via the factory
        $factory = new PasswordHasherFactory([
            'auto' => ['algorithm' => 'auto'],
        ]);

        $hasher = $factory->getPasswordHasher('auto');
        $hash= $hasher->hash($plainPassword);

        $credential->setPasswordHash($hash);

        return true;
    }
}
