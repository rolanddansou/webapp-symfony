<?php

namespace App\Feature\Media\DTO;

use App\Entity\Media\FileVersion;
use App\Feature\Media\Service\FileManagerInterface;

final readonly class FileVersionResponse
{
    public function __construct(
        public string $variant,
        public FileObjectResponse $file,
    ) {}

    /**
     * Create from FileVersion entity
     */
    public static function fromEntity(
        FileVersion $version,
        FileManagerInterface $fileManager,
        ?string $altText = null
    ): self {
        return new self(
            variant: $version->getVariant(),
            file: FileObjectResponse::fromVersion($version, $fileManager, $altText),
        );
    }
}
