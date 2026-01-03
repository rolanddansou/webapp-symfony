<?php

namespace App\Entity\System\Enum;

enum ContactMessageStatus: string
{
    case NEW = 'new';
    case READ = 'read';
    case REPLIED = 'replied';

    public function getLabel(): string
    {
        return match ($this) {
            self::NEW => 'Nouveau',
            self::READ => 'Lu',
            self::REPLIED => 'Répondu',
        };
    }
}
