<?php

namespace App\Controller\Api;

use App\Feature\Access\DTO\DeviceRequest;
use App\Feature\Access\DTO\DeviceResponse;
use App\Feature\Access\Service\IdentityUser;
use App\Feature\Access\Service\UserDeviceServiceInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/devices', name: 'api_devices_')]
#[OA\Tag(name: 'Devices', description: 'User devices management')]
final class UserDeviceController extends AbstractController
{
    public function __construct(
        private readonly UserDeviceServiceInterface $userDeviceService,
        private readonly ValidatorInterface $validator,
    ) {
    }

    #[Route('', name: 'register', methods: ['POST'])]
    #[OA\Post(
        description: 'Register or update a user device for push notifications',
        summary: 'Register device',
        requestBody: new OA\RequestBody(
            description: 'Device information',
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'deviceId', type: 'string', example: 'device_uuid_123'),
                    new OA\Property(property: 'platform', type: 'string', enum: ['android', 'ios', 'web'], example: 'android'),
                    new OA\Property(property: 'pushToken', type: 'string', nullable: true, example: 'fcm_token_xyz'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Device registered',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', ref: new Model(type: DeviceResponse::class), type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Invalid input'),
            new OA\Response(response: 401, description: 'Not authenticated'),
        ]
    )]
    public function register(
        Request $request,
        #[CurrentUser] ?IdentityUser $user
    ): JsonResponse {
        if (!$user) {
            return $this->json(['success' => false, 'error' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $data = $request->toArray();
        $deviceRequest = new DeviceRequest(
            deviceId: $data['deviceId'] ?? '',
            platform: $data['platform'] ?? '',
            pushToken: $data['pushToken'] ?? null,
        );

        $errors = $this->validator->validate($deviceRequest);
        if (count($errors) > 0) {
            return $this->json(['success' => false, 'error' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $device = $this->userDeviceService->registerDevice($user->getIdentity(), $deviceRequest);

        return $this->json([
            'success' => true,
            'data' => DeviceResponse::fromEntity($device),
        ]);
    }

    #[Route('', name: 'list', methods: ['GET'])]
    #[OA\Get(
        description: 'List all active devices for the current user',
        summary: 'List devices',
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of devices',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: new Model(type: DeviceResponse::class))
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Not authenticated'),
        ]
    )]
    public function list(
        #[CurrentUser] ?IdentityUser $user
    ): JsonResponse {
        if (!$user) {
            return $this->json(['success' => false, 'error' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $devices = $this->userDeviceService->getUserDevices($user->getIdentity());

        return $this->json([
            'success' => true,
            'data' => array_map(fn($d) => DeviceResponse::fromEntity($d), $devices),
        ]);
    }

    #[Route('/{deviceId}', name: 'delete', methods: ['DELETE'])]
    #[OA\Delete(
        description: 'Remove a device',
        summary: 'Delete device',
        parameters: [
            new OA\Parameter(
                name: 'deviceId',
                description: 'Device ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Device removed',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Not authenticated'),
            new OA\Response(response: 403, description: 'Access denied'),
            new OA\Response(response: 404, description: 'Device not found'),
        ]
    )]
    public function delete(
        string $deviceId,
        #[CurrentUser] ?IdentityUser $user
    ): JsonResponse {
        if (!$user) {
            return $this->json(['success' => false, 'error' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $this->userDeviceService->removeDevice($user->getIdentity(), $deviceId);

        return $this->json(['success' => true]);
    }
}
