<?php

namespace App\Feature\Shared\Service;

use App\Entity\System\ContactMessage;
use App\Feature\Shared\DTO\ContactMessageRequest;
use App\Feature\Shared\Event\ContactMessageCreatedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

final readonly class ContactMessageService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function createMessage(ContactMessageRequest $request): ContactMessage
    {
        $message = new ContactMessage();
        $message->setName($request->name);
        $message->setEmail($request->email);
        $message->setSubject($request->subject);
        $message->setMessage($request->message);

        $this->entityManager->persist($message);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(
            new ContactMessageCreatedEvent($message),
            ContactMessageCreatedEvent::NAME
        );

        return $message;
    }
}
