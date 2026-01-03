<?php

namespace App\Feature\Activity\DTO;

use App\Entity\Activity\UserActivity;

final readonly class ActivityResponse
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public string             $id,
        public string             $type,
        public string             $typeLabel,
        public ?array             $payload,
        public ?string            $actorId,
        public ?string            $actorType,
        public \DateTimeImmutable $occurredAt,
    ) {}

    public static function fromEntity(UserActivity $activity): self
    {
        return new self(
            id: (string) $activity->getId(),
            type: $activity->getType(),
            typeLabel: self::getTypeLabel($activity->getType()),
            payload: !empty($activity->getPayload()) ? $activity->getPayload() : null,
            actorId: $activity->getActorId(),
            actorType: $activity->getActorType(),
            occurredAt: $activity->getOccurredAt(),
        );
    }

    private static function getTypeLabel(string $type): string
    {
        return match ($type) {
            'VOUCHER_GENERATED' => 'Bon généré',
            'POINTS_ADDED' => 'Points ajoutés',
            'POINTS_SPENT' => 'Points dépensés',
            'SCAN_COMPLETED' => 'Scan effectué',
            'REWARD_REDEEMED' => 'Récompense utilisée',
            'TIER_UP' => 'Niveau augmenté',
            'PROFILE_UPDATED' => 'Profil mis à jour',
            'TRANSACTION_CREATED' => 'Transaction créée',
            'MANUAL_NOTE' => 'Note manuelle',
            'EMAIL_VERIFIED' => 'Email vérifié',
            'PASSWORD_CHANGED' => 'Mot de passe modifié',
            'LOGIN' => 'Connexion',
            'LOGOUT' => 'Déconnexion',
            'CARD_ISSUED' => 'Carte émise',
            'REGISTER' => 'Inscription',
            default => 'Autre',
        };
    }
}
