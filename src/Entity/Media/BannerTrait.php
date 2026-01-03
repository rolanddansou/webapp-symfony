<?php

namespace App\Entity\Media;

use Doctrine\ORM\Mapping as ORM;

trait BannerTrait
{
    #[ORM\OneToOne(targetEntity: MediaInterface::class, cascade: ['persist', 'remove'], fetch: "EAGER")]
    #[ORM\Cache(usage: "NONSTRICT_READ_WRITE", region: "write_rare")]
    private MediaInterface|null $banner = null;

    public function getBanner(): MediaInterface|null
    {
        return $this->banner;
    }

    public function setBanner(MediaInterface|null $banner): static
    {
        $this->banner = $banner;

        return $this;
    }
}
