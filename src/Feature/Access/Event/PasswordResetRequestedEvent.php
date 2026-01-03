<?php

namespace App\Feature\Access\Event;

use App\Entity\Access\Identity;
use Symfony\Contracts\EventDispatcher\Event;

final class PasswordResetRequestedEvent extends Event
{
    public const NAME = 'access.password.reset_requested';

    public function __construct(
        public readonly Identity $user,
        public readonly string $resetToken,
    ) {}
}
