<?php

namespace App\Feature\Media\DTO;

use App\Entity\Media\ValueObject\FileSource;
use App\Feature\Media\Exception\ValidationException;

class FileUploadRequest
{
    public FileSource $source;

    public ?string $localPath = null;
    public ?string $remoteUrl = null;
    public ?string $originalFilename = null;

    // Validation
    public array $allowedMimeTypes = [];
    public ?int $maxSize = null;
    public ?int $minSize = null;

    // Options de traitement
    public array $options = [
        'generateThumbnails' => false,
        'convertToWebp' => false,
        'customFolder' => null,
        'optimize' => true,
    ];

    public function __construct(FileSource $source)
    {
        $this->source = $source;
    }

    public function validate(): void
    {
        if ($this->source === FileSource::UPLOAD && !$this->localPath) {
            throw new ValidationException("localPath is required for UPLOAD source");
        }

        if ($this->source === FileSource::REMOTE_URL && !$this->remoteUrl) {
            throw new ValidationException("remoteUrl is required for REMOTE_URL source");
        }

        if ($this->localPath && !file_exists($this->localPath)) {
            throw new ValidationException("File does not exist: {$this->localPath}");
        }
    }

    public function validateFileConstraints(string $mimeType, int $size): void
    {
        if (!empty($this->allowedMimeTypes) && !in_array($mimeType, $this->allowedMimeTypes)) {
            throw new ValidationException(
                "MIME type '{$mimeType}' not allowed. Allowed: " . implode(', ', $this->allowedMimeTypes)
            );
        }

        if ($this->maxSize && $size > $this->maxSize) {
            throw new ValidationException(
                "File size ({$size} bytes) exceeds maximum ({$this->maxSize} bytes)"
            );
        }

        if ($this->minSize && $size < $this->minSize) {
            throw new ValidationException(
                "File size ({$size} bytes) below minimum ({$this->minSize} bytes)"
            );
        }
    }
}
