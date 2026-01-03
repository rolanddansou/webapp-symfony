<?php

namespace App\Entity\System;

use App\Entity\System\Enum\StaticContentType;
use App\Feature\Helper\DateHelper;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\PostPersist;
use Doctrine\ORM\Mapping\PostUpdate;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity]
#[ORM\Table(name: 'static_content')]
#[ORM\UniqueConstraint(name: 'UNIQ_STATIC_CONTENT_TYPE', columns: ['type'])]
#[ORM\HasLifecycleCallbacks]
#[ORM\Cache(usage: "NONSTRICT_READ_WRITE", region: "write_rare")]
#[UniqueEntity(fields: ['type'], message: 'Ce type de contenu existe déjà.', ignoreNull: true)]
class StaticContent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 50, unique: true, enumType: StaticContentType::class)]
    private StaticContentType $type;

    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column(type: Types::TEXT)]
    private string $content;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): StaticContentType
    {
        return $this->type;
    }

    public function setType(StaticContentType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->updatedAt = DateHelper::nowUTC();
    }

    #[PostPersist, PostUpdate]
    public function evictCache(PostUpdateEventArgs|PostPersistEventArgs $event): void
    {
        $event->getObjectManager()->getCache()->evictEntityRegion(self::class);
    }
}
