<?php

namespace App\Feature\Access\Profile\Event;

use App\Entity\Access\UserProfile\UserProfile;
use Symfony\Contracts\EventDispatcher\Event;

final class UserProfileUpdatedEvent extends Event
{
    public const NAME = 'user.profile.updated';

    public function __construct(
        public readonly UserProfile $customer,
        public readonly array       $changedFields,
    ) {}
}
