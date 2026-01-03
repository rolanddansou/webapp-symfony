<?php

namespace App\Entity\Media;

interface MediaInterface
{
    public function getMediaId(): ?string;
    public function getMediaFileName(): ?string;
    public function getMediaHash(): ?string;
}
