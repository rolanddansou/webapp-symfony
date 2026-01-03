<?php

namespace App\Entity\Trait;

use Doctrine\ORM\Mapping as ORM;

trait SlugTrait
{
    #[ORM\Column(type: "string", nullable: true)]
    private string|null $slug = null;

    public function getSlug(): string|null
    {
        return $this->slug;
    }

    public function setSlug(string|null $slug): self
    {
        $this->slug = $slug;

        return $this;
    }
}
