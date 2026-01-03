<?php

namespace App\Entity\Trait;

use Doctrine\ORM\Mapping as ORM;

trait PositionedTrait
{
    #[ORM\Column(type: 'integer', nullable: true)]
    protected ?int $position= null;

    #[ORM\Column(type: 'integer', nullable: true)]
    protected ?int $lastPosition= null;

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getLastPosition(): ?int
    {
        return $this->lastPosition;
    }

    #[ORM\PrePersist, ORM\PreUpdate]
    public function onPersist(): void
    {
        if(isset($this->position)){
            $this->lastPosition = $this->position;
        }
    }
}
