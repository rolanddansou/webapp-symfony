<?php

namespace App\Entity\Media;

use App\Entity\Media\ValueObject\FileSource;
use App\Entity\Trait\IdTrait;
use App\Repository\Media\FileObjectRepository;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\PostPersist;
use Doctrine\ORM\Mapping\PostUpdate;

#[ORM\Entity(repositoryClass: FileObjectRepository::class)]
#[ORM\Table(name: 'media_file')]
#[ORM\HasLifecycleCallbacks]
#[ORM\Cache(usage: "NONSTRICT_READ_WRITE", region: "write_rare")]
class FileObject implements MediaInterface
{
    use IdTrait;

    #[ORM\Column(length: 255)]
    private string $filename;

    #[ORM\Column(length: 150)]
    private string $mimeType;

    #[ORM\Column]
    private int $size;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $storagePath = null;

    #[ORM\Column(length: 50)]
    private string $storageDriver; // "local", "s3", "cloudinary"

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $hash = null; // Optional hash for file integrity

    #[ORM\Column(type: "string", enumType: FileSource::class)]
    private FileSource $source;

    #[ORM\Column(type: "json", nullable: true)]
    private ?array $metadata = [];

    #[ORM\Column(type: "datetime_immutable")]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    private ?\DateTimeImmutable $deletedAt = null;

    public function __construct(
        string $filename,
        string $mimeType,
        int $size,
        string $storageDriver,
        FileSource $source,
        ?string $storagePath = null,
        ?string $hash = null,
        array $metadata = []
    ) {
        $this->filename = $filename;
        $this->mimeType = $mimeType;
        $this->size = $size;
        $this->storageDriver = $storageDriver;
        $this->storagePath = $storagePath;
        $this->source = $source;
        $this->hash = $hash;
        $this->metadata = $metadata;
        $this->createdAt = new \DateTimeImmutable();
    }

    // Méthodes utiles
    public function isImage(): bool
    {
        return str_starts_with($this->mimeType, 'image/');
    }

    public function isVideo(): bool
    {
        return str_starts_with($this->mimeType, 'video/');
    }

    public function isPdf(): bool
    {
        return $this->mimeType === 'application/pdf';
    }

    public function getSizeInMB(): float
    {
        return round($this->size / 1024 / 1024, 2);
    }

    public function getSizeInKB(): float
    {
        return round($this->size / 1024, 2);
    }

    public function getExtension(): string
    {
        return pathinfo($this->filename, PATHINFO_EXTENSION);
    }

    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }

    public function softDelete(): void
    {
        $this->deletedAt = new \DateTimeImmutable();
    }

    public function restore(): void
    {
        $this->deletedAt = null;
    }

    // Getters
    public function getId(): string { return $this->id; }
    public function getFilename(): string { return $this->filename; }
    public function getMimeType(): string { return $this->mimeType; }
    public function getSize(): int { return $this->size; }
    public function getStoragePath(): ?string { return $this->storagePath; }
    public function getStorageDriver(): string { return $this->storageDriver; }
    public function getHash(): ?string { return $this->hash; }
    public function getSource(): FileSource { return $this->source; }
    public function getMetadata(): array { return $this->metadata ?? []; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }

    public function getMediaId(): string|null
    {
        return $this->id;
    }

    public function getMediaFileName(): string|null
    {
        return $this->filename;
    }

    public function getMediaHash(): string|null
    {
        return $this->hash;
    }

    #[PostPersist, PostUpdate]
    public function evictCache(PostUpdateEventArgs|PostPersistEventArgs $event): void
    {
        $event->getObjectManager()->getCache()->evictEntityRegion(self::class);
    }
}
