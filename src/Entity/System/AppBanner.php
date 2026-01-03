<?php

namespace App\Entity\System;

use App\Entity\Media\MediaInterface;
use App\Entity\Trait\EnabledDisabledTrait;
use App\Entity\Trait\IdTrait;
use App\Entity\Trait\TimestampTrait;
use App\Repository\System\AppBannerRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\PostPersist;
use Doctrine\ORM\Mapping\PostUpdate;

#[ORM\Entity(repositoryClass: AppBannerRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Cache(usage: "NONSTRICT_READ_WRITE", region: "write_rare")]
class AppBanner
{
    use IdTrait;
    use TimestampTrait;
    use EnabledDisabledTrait;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $actionLink = null;

    #[ORM\OneToOne(targetEntity: MediaInterface::class, cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: true)]
    #[ORM\Cache(usage: "NONSTRICT_READ_WRITE", region: "write_rare")]
    private ?MediaInterface $image = null;

    #[ORM\Column]
    private int $position = 0;

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getActionLink(): ?string
    {
        return $this->actionLink;
    }

    public function setActionLink(?string $actionLink): static
    {
        $this->actionLink = $actionLink;
        return $this;
    }

    public function getImage(): ?MediaInterface
    {
        return $this->image;
    }

    public function setImage(?MediaInterface $image): static
    {
        $this->image = $image;
        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;
        return $this;
    }

    #[PostPersist, PostUpdate]
    public function evictCache(PostUpdateEventArgs|PostPersistEventArgs $event): void
    {
        $event->getObjectManager()->getCache()->evictEntityRegion(self::class);
    }
}
