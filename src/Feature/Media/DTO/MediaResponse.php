<?php

namespace App\Feature\Media\DTO;

use App\Entity\Media\FileObject;
use App\Feature\Media\Service\FileManagerInterface;
use App\Repository\Media\FileVersionRepository;

final readonly class MediaResponse
{
    /**
     * @param FileVersionResponse[] $variants
     */
    public function __construct(
        public FileObjectResponse $original,
        public array $variants,
    ) {}

    /**
     * Create from FileObject entity
     *
     * @param FileObject $file The main file
     * @param FileManagerInterface $fileManager Service to generate URLs
     * @param FileVersionRepository|null $versionRepo Repository to load variants (optional)
     * @param string|null $altText Optional alt text from parent entity
     */
    public static function fromEntity(
        FileObject $file,
        FileManagerInterface $fileManager,
        ?FileVersionRepository $versionRepo = null,
        ?string $altText = null
    ): self {
        // Original file
        $original = FileObjectResponse::fromEntity($file, $fileManager, $altText);

        // Load variants if repository provided
        $variants = [];
        if ($versionRepo !== null) {
            $fileVersions = $versionRepo->findBy(['parent' => $file]);

            foreach ($fileVersions as $version) {
                $variants[] = FileVersionResponse::fromEntity($version, $fileManager, $altText);
            }
        }

        return new self(
            original: $original,
            variants: $variants,
        );
    }

    /**
     * Get a specific variant by name
     *
     * @param string $variantName Variant name (e.g., "thumbnail", "medium")
     * @return FileVersionResponse|null
     */
    public function getVariant(string $variantName): ?FileVersionResponse
    {
        foreach ($this->variants as $variant) {
            if ($variant->variant === $variantName) {
                return $variant;
            }
        }
        return null;
    }

    /**
     * Get thumbnail variant (shortcut)
     */
    protected function getThumbnail(): ?FileObjectResponse
    {
        return $this->getVariant('thumbnail')?->file;
    }
}
