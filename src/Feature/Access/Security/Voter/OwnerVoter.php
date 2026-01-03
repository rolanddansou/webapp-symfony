<?php

namespace App\Feature\Access\Security\Voter;

use App\Entity\Access\Identity;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Voter pour vérifier la propriété d'une ressource.
 *
 * Usage dans un controller:
 * $this->denyAccessUnlessGranted('OWNER', $resource);
 */
final class OwnerVoter extends Voter
{
    public const OWNER = 'OWNER';
    public const VIEW = 'VIEW_OWNER';
    public const EDIT = 'EDIT_OWNER';
    public const DELETE = 'DELETE_OWNER';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // Ce voter gère les attributs OWNER, VIEW_OWNER, EDIT_OWNER, DELETE_OWNER
        return in_array($attribute, [self::OWNER, self::VIEW, self::EDIT, self::DELETE]);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // L'utilisateur doit être authentifié
        if (!$user instanceof Identity) {
            return false;
        }

        // Les SUPER_ADMIN ont tous les droits
        if (in_array('ROLE_SUPER_ADMIN', $user->getRoles(), true)) {
            return true;
        }

        // Vérifier si le sujet a une méthode pour récupérer le propriétaire
        if (!method_exists($subject, 'getOwner') && !method_exists($subject, 'getUser') && !method_exists($subject, 'getIdentity')) {
            return false;
        }

        // Récupérer le propriétaire selon la méthode disponible
        $owner = null;
        if (method_exists($subject, 'getOwner')) {
            $owner = $subject->getOwner();
        } elseif (method_exists($subject, 'getUser')) {
            $owner = $subject->getUser();
        } elseif (method_exists($subject, 'getIdentity')) {
            $owner = $subject->getIdentity();
        }

        if (!$owner instanceof Identity) {
            return false;
        }

        // Vérifier que l'utilisateur est le propriétaire
        $isOwner = $owner->getId()->equals($user->getId());

        return match ($attribute) {
            self::OWNER, self::VIEW => $isOwner,
            self::EDIT => $isOwner || in_array('ROLE_ADMIN', $user->getRoles(), true),
            self::DELETE => $isOwner || in_array('ROLE_ADMIN', $user->getRoles(), true),
            default => false,
        };
    }
}

