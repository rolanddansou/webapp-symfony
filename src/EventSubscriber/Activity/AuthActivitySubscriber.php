<?php

namespace App\EventSubscriber\Activity;

use App\Entity\Activity\ActivityType;
use App\Feature\Access\Event\EmailVerifiedEvent;
use App\Feature\Access\Event\PasswordResetCodeSentEvent;
use App\Feature\Access\Event\UserLoggedInEvent;
use App\Feature\Access\Event\UserLoggedOutEvent;
use App\Feature\Access\Event\UserRegisteredEvent;
use App\Feature\Activity\ActivityRecorder;
use App\Feature\Helper\DateHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class AuthActivitySubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ActivityRecorder $activityRecorder,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UserRegisteredEvent::NAME => 'onUserRegistered',
            UserLoggedInEvent::NAME => 'onUserLoggedIn',
            UserLoggedOutEvent::NAME => 'onUserLoggedOut',
            EmailVerifiedEvent::NAME => 'onEmailVerified',
            PasswordResetCodeSentEvent::NAME => 'onPasswordResetRequested',
        ];
    }

    public function onUserRegistered(UserRegisteredEvent $event): void
    {
        $user = $event->user;

        $this->activityRecorder->record(
            userId: $user->getUserId(),
            type: ActivityType::REGISTER,
            payload: [
                'email' => $user->getUserEmail(),
                'device_id' => $event->deviceId,
                'registered_at' => DateHelper::nowUTC()->format(\DateTimeInterface::ATOM),
            ],
            actorType: 'system'
        );
    }

    public function onUserLoggedIn(UserLoggedInEvent $event): void
    {
        $user = $event->user;

        $this->activityRecorder->record(
            userId: $user->getUserId(),
            type: ActivityType::LOGIN,
            payload: [
                'device_id' => $event->deviceId,
                'ip_address' => $event->ipAddress,
                'logged_in_at' => DateHelper::nowUTC()->format(\DateTimeInterface::ATOM),
            ],
            actorType: 'user'
        );
    }

    public function onUserLoggedOut(UserLoggedOutEvent $event): void
    {
        $user = $event->user;

        $this->activityRecorder->record(
            userId: $user->getUserId(),
            type: ActivityType::LOGOUT,
            payload: [
                'logged_out_at' => DateHelper::nowUTC()->format(\DateTimeInterface::ATOM),
            ],
            actorType: 'user'
        );
    }

    public function onEmailVerified(EmailVerifiedEvent $event): void
    {
        $user = $event->user;

        $this->activityRecorder->record(
            userId: $user->getUserId(),
            type: ActivityType::EMAIL_VERIFIED,
            payload: [
                'email' => $user->getUserEmail(),
                'verified_at' => DateHelper::nowUTC()->format(\DateTimeInterface::ATOM),
            ],
            actorType: 'user'
        );
    }

    public function onPasswordResetRequested(PasswordResetCodeSentEvent $event): void
    {
        $user = $event->user;

        $this->activityRecorder->record(
            userId: $user->getUserId(),
            type: ActivityType::PASSWORD_CHANGED,
            payload: [
                'action' => 'reset_requested',
                'requested_at' => DateHelper::nowUTC()->format(\DateTimeInterface::ATOM),
            ],
            actorType: 'user'
        );
    }
}
