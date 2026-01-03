<?php

namespace App\Feature\Access\Event;

use App\Entity\Access\Identity;
use Symfony\Contracts\EventDispatcher\Event;

final class EmailVerifiedEvent extends Event
{
    public const NAME = 'user.email_verified';

    public function __construct(
        public readonly Identity $user,
    ) {}
}
