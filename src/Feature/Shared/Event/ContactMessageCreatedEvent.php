<?php

namespace App\Feature\Shared\Event;

use App\Entity\System\ContactMessage;
use Symfony\Contracts\EventDispatcher\Event;

final class ContactMessageCreatedEvent extends Event
{
    public const NAME = 'contact_message.created';

    public function __construct(
        public readonly ContactMessage $message
    ) {
    }
}
