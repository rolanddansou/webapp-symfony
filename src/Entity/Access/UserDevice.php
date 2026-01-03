<?php

namespace App\Entity\Access;

use App\Entity\Trait\EnabledDisabledTrait;
use App\Entity\Trait\IdTrait;
use App\Entity\Trait\TimestampTrait;
use App\Repository\Access\UserDeviceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserDeviceRepository::class)]
#[ORM\HasLifecycleCallbacks]
class UserDevice
{
    use IdTrait;
    use TimestampTrait;
    use EnabledDisabledTrait;

    #[ORM\Column(length: 200)]
    private string $deviceId;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $pushToken = null;

    #[ORM\Column(length: 50)]
    private string $platform; // iOS, Android, Web

    #[ORM\ManyToOne(inversedBy: 'devices')]
    private ?Identity $relativeUser = null;

    public function getRelativeUser(): ?Identity
    {
        return $this->relativeUser;
    }

    public function setRelativeUser(?Identity $relativeUser): static
    {
        $this->relativeUser = $relativeUser;

        return $this;
    }

    public function getDeviceId(): string
    {
        return $this->deviceId;
    }

    public function setDeviceId(string $deviceId): void
    {
        $this->deviceId = $deviceId;
    }

    public function getPushToken(): ?string
    {
        return $this->pushToken;
    }

    public function setPushToken(?string $pushToken): void
    {
        $this->pushToken = $pushToken;
    }

    public function getPlatform(): string
    {
        return $this->platform;
    }

    public function setPlatform(string $platform): void
    {
        $this->platform = $platform;
    }

    public function enable(): void
    {
        $this->setIsEnabled(true);
    }

    public function disable(): void
    {
        $this->setIsEnabled(false);
    }
}
