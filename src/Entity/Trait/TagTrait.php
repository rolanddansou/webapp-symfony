<?php

namespace App\Entity\Trait;

use Doctrine\ORM\Mapping as ORM;

trait TagTrait
{
    #[ORM\Column(length: 255, nullable: true)]
    private string|null $tags = null;

    public function getTags(): ?string
    {
        return $this->tags;
    }

    public function setTags(string|null $tags): self
    {
        $this->tags = $tags;
        $this->cleanTags();

        return $this;
    }

    public function getTagsAsArray(): array
    {
        if(!$this->tags) {
            return [];
        }

        return explode(';', $this->tags);
    }

    private function cleanTags(): void
    {
        if ($this->tags) {
            $this->tags = str_replace(',', ';', $this->tags);

            if(str_ends_with($this->tags, ';')) {
                $this->tags = substr($this->tags, 0, -1);
            }

            $this->tags = trim($this->tags);

            if(empty($this->tags)) {
                $this->tags = null;
            }

            $this->tags = str_replace(';;', ';', $this->tags);

            if(str_starts_with($this->tags, ';')) {
                $this->tags = substr($this->tags, 1);
            }

            $tagArray= explode(';', $this->tags);
            $tagArray= array_map(trim(...), $tagArray);
            $tagArray= array_filter($tagArray, static fn($tag) => !empty($tag));

            $this->tags = implode(';', array_unique($tagArray));
        }
    }
}
