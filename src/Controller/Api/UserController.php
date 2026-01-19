<?php

namespace App\Controller\Api;

use App\Feature\Access\Profile\DTO\UpdateUserProfileRequest;
use App\Feature\Access\Profile\DTO\UserProfilePreferencesRequest;
use App\Feature\Access\Profile\DTO\UserProfilePreferencesResponse;
use App\Feature\Access\Profile\DTO\UserProfileResponse;
use App\Feature\Access\Profile\Service\UserProfileServiceInterface;
use App\Feature\Access\Service\IdentityUser;
use App\Repository\Access\UserProfile\UserProfileRepository;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/users', name: 'api_users_')]
#[OA\Tag(name: 'Users', description: 'User profile and preferences management')]
final class UserController extends AbstractController
{
    public function __construct(
        private readonly UserProfileServiceInterface $userProfileService,
        private readonly UserProfileRepository $userProfileRepository,
    ) {}

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    #[OA\Get(
        description: 'Returns customer profile by ID (requires authentication)',
        summary: 'Get customer profile',
        parameters: [
            new OA\Parameter(name: 'id', description: 'User ID', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'User profile',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', ref: new Model(type: UserProfileResponse::class)),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Not authenticated'),
            new OA\Response(response: 403, description: 'Access denied'),
            new OA\Response(response: 404, description: 'User not found'),
        ]
    )]
    public function show(
        string $id,
        #[CurrentUser] ?IdentityUser $user
    ): JsonResponse {
        if (!$user) {
            return $this->json([
                'success' => false,
                'error' => 'Not authenticated',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $userProfile = $this->userProfileService->getUserProfile($id);

        if (!$userProfile) {
            return $this->json([
                'success' => false,
                'error' => 'User not found',
            ], Response::HTTP_NOT_FOUND);
        }

        // Check if user can access this customer profile
        if ($userProfile->getUser()->getUserId() !== $user->getIdentity()->getUserId()) {
            if (!in_array('ROLE_ADMIN', $user->getRoles())) {
                return $this->json([
                    'success' => false,
                    'error' => 'Access denied',
                ], Response::HTTP_FORBIDDEN);
            }
        }

        return $this->json([
            'success' => true,
            'data' => UserProfileResponse::fromEntity($userProfile),
        ]);
    }

    #[Route('/me', name: 'me', methods: ['GET'], priority: 10)]
    #[OA\Get(
        description: 'Returns the profile of the authenticated customer',
        summary: 'Get my profile',
        responses: [
            new OA\Response(
                response: 200,
                description: 'User profile',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', ref: new Model(type: UserProfileResponse::class)),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Not authenticated'),
            new OA\Response(response: 404, description: 'User profile not found'),
        ]
    )]
    public function me(
        #[CurrentUser] ?IdentityUser $user
    ): JsonResponse {
        if (!$user) {
            return $this->json([
                'success' => false,
                'error' => 'Not authenticated',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $userProfile = $this->userProfileRepository->findByIdentity($user->getIdentity());

        if (!$userProfile) {
            return $this->json([
                'success' => false,
                'error' => 'User profile not found',
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'success' => true,
            'data' => UserProfileResponse::fromEntity($userProfile),
        ]);
    }

    #[OA\Put(
        description: 'Updates customer profile by ID (requires authentication)',
        summary: 'Update customer profile',
        requestBody: new OA\RequestBody(
            description: 'User profile data to update',
            required: true,
            content: new OA\JsonContent(ref: new Model(type: UpdateUserProfileRequest::class))
        ),
        parameters: [
            new OA\Parameter(name: 'id', description: 'User ID', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Updated customer profile',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', ref: new Model(type: UserProfileResponse::class)),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Not authenticated'),
            new OA\Response(response: 403, description: 'Access denied'),
            new OA\Response(response: 404, description: 'User not found'),
        ]
    )]
    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(
        string $id,
        #[MapRequestPayload] UpdateUserProfileRequest $request,
        #[CurrentUser] ?IdentityUser $user
    ): JsonResponse {
        if (!$user) {
            return $this->json([
                'success' => false,
                'error' => 'Not authenticated',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $userProfile = $this->userProfileService->getUserProfile($id);

        if (!$userProfile) {
            return $this->json([
                'success' => false,
                'error' => 'User not found',
            ], Response::HTTP_NOT_FOUND);
        }

        // Only allow updating own profile
        if ($userProfile->getUser()->getUserId() !== $user->getIdentity()->getUserId()) {
            return $this->json([
                'success' => false,
                'error' => 'Access denied',
            ], Response::HTTP_FORBIDDEN);
        }

        $customer = $this->userProfileService->updateProfile($userProfile, $request);

        return $this->json([
            'success' => true,
            'data' => UserProfileResponse::fromEntity($userProfile),
        ]);
    }

    #[Route('/{id}/preferences', name: 'preferences_show', methods: ['GET'])]
    public function showPreferences(
        string $id,
        #[CurrentUser] ?IdentityUser $user
    ): JsonResponse {
        if (!$user) {
            return $this->json([
                'success' => false,
                'error' => 'Not authenticated',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $userProfile = $this->userProfileService->getUserProfile($id);

        if (!$userProfile) {
            return $this->json([
                'success' => false,
                'error' => 'User not found',
            ], Response::HTTP_NOT_FOUND);
        }

        // Only allow viewing own preferences
        if ($userProfile->getUser()->getUserId() !== $user->getIdentity()->getUserId()) {
            return $this->json([
                'success' => false,
                'error' => 'Access denied',
            ], Response::HTTP_FORBIDDEN);
        }

        $preferences = $userProfile->getPreferences();

        return $this->json([
            'success' => true,
            'data' => $preferences ? UserProfilePreferencesResponse::fromEntity($preferences) : null,
        ]);
    }

    #[Route('/{id}/preferences', name: 'preferences_update', methods: ['PUT'])]
    public function updatePreferences(
        string $id,
        #[MapRequestPayload] UserProfilePreferencesRequest $request,
        #[CurrentUser] ?IdentityUser $user
    ): JsonResponse {
        if (!$user) {
            return $this->json([
                'success' => false,
                'error' => 'Not authenticated',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $userProfile = $this->userProfileService->getUserProfile($id);

        if (!$userProfile) {
            return $this->json([
                'success' => false,
                'error' => 'User not found',
            ], Response::HTTP_NOT_FOUND);
        }

        // Only allow updating own preferences
        if ($userProfile->getUser()->getUserId() !== $user->getIdentity()->getUserId()) {
            return $this->json([
                'success' => false,
                'error' => 'Access denied',
            ], Response::HTTP_FORBIDDEN);
        }

        $userProfile = $this->userProfileService->updatePreferencesFromRequest($userProfile, $request);

        return $this->json([
            'success' => true,
            'data' => UserProfilePreferencesResponse::fromEntity($userProfile->getPreferences()),
        ]);
    }
}
