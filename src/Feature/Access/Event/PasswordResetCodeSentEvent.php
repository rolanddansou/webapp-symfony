<?php

namespace App\Feature\Access\Event;

use App\Entity\Access\Identity;
use Symfony\Contracts\EventDispatcher\Event;

final class PasswordResetCodeSentEvent extends Event
{
    public const NAME = 'user.password_reset_code_sent';

    public function __construct(
        public readonly Identity $user,
        public readonly string $code,
    ) {}
}
