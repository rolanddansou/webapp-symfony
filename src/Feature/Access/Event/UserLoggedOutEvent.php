<?php

namespace App\Feature\Access\Event;

use App\Entity\Access\Identity;
use Symfony\Contracts\EventDispatcher\Event;

final class UserLoggedOutEvent extends Event
{
    public const NAME = 'access.user.logged_out';

    public function __construct(
        public readonly Identity $user,
        public readonly string $deviceId,
    ) {}
}
