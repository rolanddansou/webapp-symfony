<?php

namespace App\Entity\Media;

use Doctrine\ORM\Mapping as ORM;

trait MediaTrait
{
    #[ORM\OneToOne(cascade: ['persist', 'remove'], fetch: "EAGER")]
    private MediaInterface|null $media = null;

    public function getMedia(): MediaInterface|null
    {
        return $this->media;
    }

    public function setMedia(MediaInterface|null $media): static
    {
        $this->media = $media;

        return $this;
    }
}
