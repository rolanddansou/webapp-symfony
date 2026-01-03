<?php

namespace App\Feature\Media\Event;

use App\Entity\Media\FileObject;
use Symfony\Contracts\EventDispatcher\Event;

class FileDeletedEvent extends Event
{
    const NAME = 'media.file.deleted';

    public function __construct(
        public FileObject $file,
        public bool       $hardDelete
    ) {}
}
