<?php

namespace App\Entity\Trait;

use Doctrine\ORM\Mapping\Column;

trait EnabledDisabledTrait
{
    #[Column(type: 'boolean', options: ['default' => true])]
    protected bool $isEnabled= true;

    public function getIsEnabled(): ?bool
    {
        return $this->isEnabled;
    }

    public function getEnabled(): ?bool
    {
        return $this->isEnabled;
    }

    public function setIsEnabled(bool $isEnabled): self
    {
        $this->isEnabled = $isEnabled;

        return $this;
    }

    public function setEnabled(bool $isEnabled): self
    {
        $this->isEnabled = $isEnabled;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }
}
