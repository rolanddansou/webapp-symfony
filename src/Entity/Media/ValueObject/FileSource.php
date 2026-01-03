<?php

namespace App\Entity\Media\ValueObject;

enum FileSource: string
{
    case UPLOAD = "upload";
    case REMOTE_URL = "remote_url";
    case SYSTEM_GENERATED = "system_generated";
    case IMPORTED = "imported";
    case AI_GENERATED = "ai_generated";
}
