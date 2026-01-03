<?php

namespace App\Feature\Media\Event;

use App\Entity\Media\FileObject;
use App\Entity\Media\ValueObject\FileSource;
use Symfony\Contracts\EventDispatcher\Event;

class FileUploadedEvent extends Event
{
    const NAME = 'media.file.uploaded';

    public function __construct(
        public FileObject $file,
        public FileSource $source
    ) {}
}
