<?php

namespace App\Controller\Api;

use App\Feature\Access\Service\IdentityUser;
use App\Feature\Activity\DTO\ActivityListResponse;
use App\Feature\Activity\DTO\ActivityResponse;
use App\Feature\Activity\Service\ActivityService;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/activities', name: 'api_activities_')]
#[OA\Tag(name: 'Activities', description: 'User activity tracking and history')]
final class ActivityController extends AbstractController
{
    public function __construct(
        private readonly ActivityService $activityService,
    ) {}

    #[OA\Get(
        description: 'Returns paginated list of user activities',
        summary: 'List all user activities',
        parameters: [
            new OA\Parameter(
                name: 'page',
                description: 'Page number (default: 1)',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 1, minimum: 1)
            ),
            new OA\Parameter(
                name: 'limit',
                description: 'Results per page (default: 20, max: 50)',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 20, maximum: 50, minimum: 1)
            ),
            new OA\Parameter(
                name: 'type',
                description: 'Filter by activity type',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', example: 'POINTS_ADDED')
            ),
            new OA\Parameter(
                name: 'from',
                description: 'Filter activities from this date (YYYY-MM-DD)',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', format: 'date', example: '2024-01-01')
            ),
            new OA\Parameter(
                name: 'to',
                description: 'Filter activities up to this date (YYYY-MM-DD)',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', format: 'date', example: '2024-12-31')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response with paginated activities',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', ref: new Model(type: ActivityListResponse::class), type: 'object')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Not authenticated'),
        ]
    )]
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(
        Request $request,
        #[CurrentUser] ?IdentityUser $user
    ): JsonResponse {
        if (!$user) {
            return $this->json([
                'success' => false,
                'error' => 'Not authenticated',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $page = max(1, $request->query->getInt('page', 1));
        $limit = min(50, max(1, $request->query->getInt('limit', 20)));
        $type = $request->query->get('type');

        $from = null;
        $to = null;

        if ($request->query->has('from')) {
            try {
                $from = new \DateTimeImmutable($request->query->get('from'));
            } catch (\Exception) {}
        }

        if ($request->query->has('to')) {
            try {
                $to = new \DateTimeImmutable($request->query->get('to'));
            } catch (\Exception) {}
        }

        $response = $this->activityService->getForUser(
            $user->getIdentity(),
            $page,
            $limit,
            $type,
            $from,
            $to
        );

        return $this->json([
            'success' => true,
            'data' => $response,
        ]);
    }

    #[OA\Get(
        description: 'Returns details of a specific user activity by ID',
        summary: 'Get activity details',
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Activity ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response with activity details',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', ref: new Model(type: ActivityResponse::class), type: 'object')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Not authenticated'),
            new OA\Response(response: 403, description: 'Access denied'),
            new OA\Response(response: 404, description: 'Activity not found'),
        ]
    )]
    #[Route('/{id}', name: 'show', methods: ['GET'])]
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

        $activity = $this->activityService->getById($id);

        if (!$activity) {
            return $this->json([
                'success' => false,
                'error' => 'Activity not found',
            ], Response::HTTP_NOT_FOUND);
        }

        // Check ownership
        if ($activity->getUserId() !== $user->getIdentity()->getUserId()) {
            return $this->json([
                'success' => false,
                'error' => 'Access denied',
            ], Response::HTTP_FORBIDDEN);
        }

        return $this->json([
            'success' => true,
            'data' => ActivityResponse::fromEntity($activity),
        ]);
    }
}
