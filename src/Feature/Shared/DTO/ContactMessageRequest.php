<?php

namespace App\Feature\Shared\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final class ContactMessageRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Votre nom est requis')]
        #[Assert\Length(max: 255)]
        public readonly string $name,

        #[Assert\NotBlank(message: 'Votre email est requis')]
        #[Assert\Email(message: 'L\'email n\'est pas valide')]
        #[Assert\Length(max: 255)]
        public readonly string $email,

        #[Assert\NotBlank(message: 'Le sujet est requis')]
        #[Assert\Length(max: 255)]
        public readonly string $subject,

        #[Assert\NotBlank(message: 'Le message est requis')]
        #[Assert\Length(min: 10, minMessage: 'Votre message doit faire au moins 10 caractères')]
        #[Assert\Length(max: 5000)]
        public readonly string $message,
    ) {
    }
}
