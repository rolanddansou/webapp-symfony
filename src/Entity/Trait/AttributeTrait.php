<?php

namespace App\Entity\Trait;

use Doctrine\ORM\Mapping as ORM;

trait AttributeTrait
{
    #[ORM\Column(type: "json", nullable: true)]
    private array $attributes = [];

    public function getAttributes(): array
    {
        return $this->attributes ?? [];
    }

    public function setAttributes(?array $attributes): self
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function getAttribute(string $key)
    {
        return $this->attributes[$key] ?? null;
    }

    public function setAttribute(string $key, string $value): self
    {
        if(!is_array($this->attributes)) {
            $this->attributes = [];
        }

        $this->attributes[$key] = $value;

        return $this;
    }

    public function removeAttribute(string $key): self
    {
        unset($this->attributes[$key]);

        return $this;
    }

    public function hasAttribute(string $key): bool
    {
        return isset($this->attributes[$key]);
    }

    public function getAttributesKeys(): array
    {
        return array_keys($this->attributes);
    }

    public function getAttributesValues(): array
    {
        return array_values($this->attributes);
    }
}
