<?php

namespace App\Feature\Shared\EventSubscriber;

use App\Feature\Shared\Event\ContactMessageCreatedEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class ContactMessageSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private MailerInterface $mailer,
        private LoggerInterface $logger,
        #[Autowire('%app.support_email%')]
        private string $supportEmail,
        #[Autowire('%app_name%')]
        private string $appName
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ContactMessageCreatedEvent::NAME => 'onMessageCreated',
        ];
    }

    public function onMessageCreated(ContactMessageCreatedEvent $event): void
    {
        $contact = $event->message;

        $email = (new Email())
            ->to(new Address($this->supportEmail, "Support $this->appName"))
            ->subject("[$this->appName] Nouveau message de contact : {$contact->getSubject()}")
            ->html($this->renderEmailBody($contact));

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Erreur lors de l\'envoi de l\'email de nouveau message de contact : ' . $e->getMessage(), [
                'exception' => $e,
                'contact_id' => $contact->getId(),
            ]);
        }
    }

    private function renderEmailBody($contact): string
    {
        return "
            <h1>Nouveau message de contact</h1>
            <p>Un nouveau message a été envoyé via le formulaire de contact de <strong>$this->appName</strong>.</p>
            <ul>
                <li><strong>Nom :</strong> {$contact->getName()}</li>
                <li><strong>Email :</strong> {$contact->getEmail()}</li>
                <li><strong>Sujet :</strong> {$contact->getSubject()}</li>
            </ul>
            <p><strong>Message :</strong><br>" . nl2br($contact->getMessage()) . "</p>
            <hr>
            <p>Vous pouvez gérer ce message dans l'interface d'administration.</p>
        ";
    }
}
