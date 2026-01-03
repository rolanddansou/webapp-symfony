<?php

namespace App\Feature\Access\DTO;

use App\Entity\Access\Identity;

final readonly class UserProfileResponse
{
    /**
     * @param array<string, mixed> $profile
     * ex: ['fullName' => 'John Doe', 'phone' => '+1234567890']
     */
    public function __construct(
        public string              $id,
        public string              $email,
        protected array               $roles,
        public ?\DateTimeImmutable $emailVerifiedAt,
        public ?\DateTimeImmutable $createdAt,
        public bool $emailVerified = false,
    ) {}

    public static function fromEntity(Identity $identity): self
    {
        $roles = [];
        foreach ($identity->getRoles() as $role) {
            $roles[] = $role->getName();
        }

        return new self(
            id: (string) $identity->getId(),
            email: $identity->getUserEmail(),
            roles: $roles,
            emailVerifiedAt: $identity->getEmailVerifiedAt(),
            createdAt: $identity->getCreatedAt(),
            emailVerified: $identity->isEmailVerified()
        );
    }
}
