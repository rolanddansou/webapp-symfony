<?php

namespace App\Feature\Media\DTO;

use App\Entity\Media\FileObject;
use App\Entity\Media\FileVersion;
use App\Feature\Media\Service\FileManagerInterface;

final readonly class FileObjectResponse
{
    public function __construct(
        public string $id,
        public string $url,
        protected string $filename,
        protected string $mimeType,
        protected int $size,
        protected float $sizeInKB,
        protected float $sizeInMB,
        protected string $extension,
        public ?string $altText,
        public ?array $metadata,
        protected \DateTimeImmutable $createdAt,
        protected bool $isImage,
        protected bool $isVideo,
        protected bool $isPdf,
    ) {}

    /**
     * Create from FileObject entity
     *
     * @param FileObject $file The file entity
     * @param FileManagerInterface $fileManager Service to generate public URL
     * @param string|null $altText Optional alt text (from parent entity)
     */
    public static function fromEntity(
        FileObject $file,
        FileManagerInterface $fileManager,
        ?string $altText = null
    ): self {
        return new self(
            id: (string) $file->getId(),
            url: $fileManager->getPublicUrl($file),
            filename: $file->getFilename(),
            mimeType: $file->getMimeType(),
            size: $file->getSize(),
            sizeInKB: $file->getSizeInKB(),
            sizeInMB: $file->getSizeInMB(),
            extension: $file->getExtension(),
            altText: $altText,
            metadata: $file->getMetadata(),
            createdAt: $file->getCreatedAt(),
            isImage: $file->isImage(),
            isVideo: $file->isVideo(),
            isPdf: $file->isPdf(),
        );
    }

    /**
     * Create from FileVersion (variant)
     *
     * @param FileVersion $version The file version
     * @param FileManagerInterface $fileManager Service to generate URL
     * @param string|null $altText Optional alt text
     */
    public static function fromVersion(
        FileVersion $version,
        FileManagerInterface $fileManager,
        ?string $altText = null
    ): self {
        $parent = $version->getParent();
        $metadata = $version->getMetadata() ?? [];

        // Générer URL du variant
        $parentUrl = $fileManager->getPublicUrl($parent);
        $variantUrl = str_replace(
            $parent->getStoragePath(),
            $version->getStoragePath(),
            $parentUrl
        );

        return new self(
            id: (string) $version->getId(),
            url: $variantUrl,
            filename: basename($version->getStoragePath()),
            mimeType: $parent->getMimeType(),
            size: $version->getSize(),
            sizeInKB: round($version->getSize() / 1024, 2),
            sizeInMB: round($version->getSize() / 1024 / 1024, 2),
            extension: pathinfo($version->getStoragePath(), PATHINFO_EXTENSION),
            altText: $altText,
            metadata: $metadata,
            createdAt: $version->getCreatedAt(),
            isImage: str_starts_with($parent->getMimeType(), 'image/'),
            isVideo: str_starts_with($parent->getMimeType(), 'video/'),
            isPdf: $parent->getMimeType() === 'application/pdf',
        );
    }
}
