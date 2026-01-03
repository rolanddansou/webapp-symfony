<?php

namespace App\EventSubscriber\Activity;

use App\Entity\Access\UserProfile\UserProfile;
use App\Entity\Activity\ActivityType;
use App\Feature\Access\Profile\Event\UserProfileUpdatedEvent;
use App\Feature\Activity\ActivityRecorder;
use App\Feature\Helper\DateHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class UserProfileActivitySubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ActivityRecorder $activityRecorder,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UserProfileUpdatedEvent::NAME => 'onUserProfileUpdated',
        ];
    }

    public function onUserProfileUpdated(UserProfileUpdatedEvent $event): void
    {
        $customer = $event->customer;

        if (!$customer instanceof UserProfile) {
            return;
        }

        $user = $customer->getUser();

        // Don't record if no meaningful fields changed
        $changedFields = $event->changedFields;
        if (empty($changedFields)) {
            return;
        }

        // Sanitize changed fields - don't log sensitive data values
        $safeFields = [];
        $sensitiveFields = ['password', 'passwordHash', 'token', 'secret'];

        foreach ($changedFields as $field => $change) {
            if (in_array($field, $sensitiveFields, true)) {
                $safeFields[$field] = '[changed]';
            } else {
                $safeFields[$field] = $change;
            }
        }

        $this->activityRecorder->record(
            userId: $user->getUserId(),
            type: ActivityType::PROFILE_UPDATED,
            payload: [
                'changed_fields' => array_keys($safeFields),
                'changes' => $safeFields,
                'updated_at' => DateHelper::nowUTC()->format(\DateTimeInterface::ATOM),
            ],
            actorType: 'user'
        );
    }
}
