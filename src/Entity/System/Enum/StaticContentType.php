<?php

namespace App\Entity\System\Enum;

enum StaticContentType: string
{
    case TERMS_OF_SERVICE = 'cgu'; // CGU
    case PRIVACY_POLICY = 'privacy';
    case LEGAL_MENTIONS = 'mentions';
}
