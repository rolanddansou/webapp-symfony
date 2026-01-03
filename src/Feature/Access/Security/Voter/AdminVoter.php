<?php

namespace App\Feature\Access\Security\Voter;

use App\Entity\Access\Identity;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Voter pour les permissions administratives avancées.
 *
 * Usage dans un controller:
 * $this->denyAccessUnlessGranted('ADMIN_ACCESS', $resource);
 */
final class AdminVoter extends Voter
{
    public const ADMIN_ACCESS = 'ADMIN_ACCESS';
    public const ADMIN_CREATE = 'ADMIN_CREATE';
    public const ADMIN_EDIT = 'ADMIN_EDIT';
    public const ADMIN_DELETE = 'ADMIN_DELETE';
    public const ADMIN_VIEW_ALL = 'ADMIN_VIEW_ALL';
    public const BACKEND_ACCESS = 'BACKEND_ACCESS';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [
            self::ADMIN_ACCESS,
            self::ADMIN_CREATE,
            self::ADMIN_EDIT,
            self::ADMIN_DELETE,
            self::ADMIN_VIEW_ALL,
            self::BACKEND_ACCESS,
        ]);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // L'utilisateur doit être authentifié
        if (!$user instanceof Identity) {
            return false;
        }

        $userRoles = $user->getRoles();

        return match ($attribute) {
            // BACKEND_ACCESS: minimum ROLE_BACKEND
            self::BACKEND_ACCESS =>
                in_array('ROLE_BACKEND', $userRoles, true)
                || in_array('ROLE_ADMIN', $userRoles, true)
                || in_array('ROLE_SUPER_ADMIN', $userRoles, true),

            // ADMIN_ACCESS: minimum ROLE_ADMIN
            self::ADMIN_ACCESS =>
                in_array('ROLE_ADMIN', $userRoles, true)
                || in_array('ROLE_SUPER_ADMIN', $userRoles, true),

            // ADMIN_VIEW_ALL: peut voir toutes les ressources
            self::ADMIN_VIEW_ALL =>
                in_array('ROLE_ADMIN', $userRoles, true)
                || in_array('ROLE_SUPER_ADMIN', $userRoles, true),

            // ADMIN_CREATE: peut créer des ressources pour autres utilisateurs
            self::ADMIN_CREATE =>
                in_array('ROLE_ADMIN', $userRoles, true)
                || in_array('ROLE_SUPER_ADMIN', $userRoles, true),

            // ADMIN_EDIT: peut modifier n'importe quelle ressource
            self::ADMIN_EDIT =>
                in_array('ROLE_ADMIN', $userRoles, true)
                || in_array('ROLE_SUPER_ADMIN', $userRoles, true),

            // ADMIN_DELETE: uniquement SUPER_ADMIN pour les suppressions critiques
            self::ADMIN_DELETE =>
                in_array('ROLE_SUPER_ADMIN', $userRoles, true),

            default => false,
        };
    }
}

