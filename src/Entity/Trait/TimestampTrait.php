<?php

namespace App\Entity\Trait;

use App\Feature\Helper\DateHelper;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;

trait TimestampTrait
{
    #[Column(type: 'datetime_immutable')]
    protected \DateTimeImmutable $createdAt;
    #[Column(type: 'datetime_immutable', nullable: true)]
    protected \DateTimeImmutable|null $updatedAt = null;

    /**
     * @return \DateTimeImmutable|null
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTimeImmutable|null $createdAt
     */
    public function setCreatedAt(?\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTimeImmutable|null $updatedAt
     */
    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    #[PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = DateHelper::nowUTC();
    }

    #[PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = DateHelper::nowUTC();
    }
}
