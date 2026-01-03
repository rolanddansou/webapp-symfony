<?php

namespace App\Entity\Media;

use App\Entity\Trait\IdTrait;
use App\Repository\Media\FileVersionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FileVersionRepository::class)]
#[ORM\Table(name: 'media_file_version')]
class FileVersion
{
    use IdTrait;

    #[ORM\ManyToOne(targetEntity: FileObject::class)]
    #[ORM\JoinColumn(nullable: false)]
    private FileObject $parent;

    #[ORM\Column(length: 50)]
    private string $variant; // "thumbnail", "medium", "large", "webp"

    #[ORM\Column(length: 255)]
    private string $storagePath;

    #[ORM\Column(length: 50)]
    private string $storageDriver;

    #[ORM\Column]
    private int $size;

    #[ORM\Column(type: "json", nullable: true)]
    private ?array $metadata = [];

    #[ORM\Column(type: "datetime_immutable")]
    private \DateTimeImmutable $createdAt;

    public function __construct(
        FileObject $parent,
        string $variant,
        string $storagePath,
        string $storageDriver,
        int $size,
        array $metadata = []
    ) {
        $this->parent = $parent;
        $this->variant = $variant;
        $this->storagePath = $storagePath;
        $this->storageDriver = $storageDriver;
        $this->size = $size;
        $this->metadata = $metadata;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getParent(): FileObject
    {
        return $this->parent;
    }

    public function setParent(FileObject $parent): void
    {
        $this->parent = $parent;
    }

    public function getVariant(): string
    {
        return $this->variant;
    }

    public function setVariant(string $variant): void
    {
        $this->variant = $variant;
    }

    public function getStoragePath(): string
    {
        return $this->storagePath;
    }

    public function setStoragePath(string $storagePath): void
    {
        $this->storagePath = $storagePath;
    }

    public function getStorageDriver(): string
    {
        return $this->storageDriver;
    }

    public function setStorageDriver(string $storageDriver): void
    {
        $this->storageDriver = $storageDriver;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function setSize(int $size): void
    {
        $this->size = $size;
    }

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function setMetadata(?array $metadata): void
    {
        $this->metadata = $metadata;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}
