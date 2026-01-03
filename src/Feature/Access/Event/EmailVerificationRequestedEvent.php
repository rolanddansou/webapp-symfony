<?php

namespace App\Feature\Access\Event;

use App\Entity\Access\Identity;
use Symfony\Contracts\EventDispatcher\Event;

final class EmailVerificationRequestedEvent extends Event
{
    public const NAME = 'user.email_verification_requested';

    public function __construct(
        public readonly Identity $user,
        public readonly string $code,
    ) {}
}
