<?php

namespace App\Entity\Media\ValueObject;

readonly class StoredFileResult
{
    public function __construct(
        public string $filename,
        public string $mimeType,
        public int $size,
        public string $storagePath,
        public array $metadata = []
    ) {}
}
