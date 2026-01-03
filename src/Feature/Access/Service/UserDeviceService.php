<?php

namespace App\Feature\Access\Service;

use App\Entity\Access\Identity;
use App\Entity\Access\IdentityInterface;
use App\Entity\Access\UserDevice;
use App\Feature\Access\DTO\DeviceRequest;
use App\Repository\Access\UserDeviceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class UserDeviceService implements UserDeviceServiceInterface
{
    public function __construct(
        private readonly UserDeviceRepository $userDeviceRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function registerDevice(IdentityInterface $user, DeviceRequest $request): UserDevice
    {
        if (!$user instanceof Identity) {
            // Should strictly be Identity entity because of association mapping
            throw new \InvalidArgumentException('User must be an instance of Identity entity.');
        }

        $device = $this->userDeviceRepository->findByDeviceId($request->deviceId);

        if (!$device) {
            $device = new UserDevice();
            $device->setDeviceId($request->deviceId);
            $device->setCreatedAt(new \DateTimeImmutable());
        }

        $device->setRelativeUser($user);
        $device->setPlatform($request->platform);
        $device->setPushToken($request->pushToken);
        $device->setUpdatedAt(new \DateTimeImmutable());
        $device->enable();

        $this->userDeviceRepository->save($device, true);

        return $device;
    }

    public function removeDevice(IdentityInterface $user, string $deviceId): void
    {
        $device = $this->userDeviceRepository->findByDeviceId($deviceId);

        if (!$device) {
            throw new NotFoundHttpException('Device not found.');
        }

        if ($device->getRelativeUser()->getUserId() !== $user->getUserId()) {
            throw new AccessDeniedHttpException('You do not own this device.');
        }

        $this->entityManager->remove($device);
        $this->entityManager->flush();
    }

    public function getUserDevices(IdentityInterface $user): array
    {
        return $this->userDeviceRepository->findActiveByUserId($user->getUserId());
    }
}
