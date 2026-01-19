<?php

namespace App\Controller\Api;

use App\Feature\Access\Service\IdentityUser;
use App\Feature\Notification\DTO\NotificationResponse;
use App\Feature\Notification\Service\NotificationService;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/notifications', name: 'api_notifications_')]
#[OA\Tag(name: 'Notifications', description: 'User notifications management')]
final class NotificationController extends AbstractController
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    #[OA\Get(
        description: 'Returns a list of recent notifications for the current user',
        summary: 'List recent notifications',
        parameters: [
            new OA\Parameter(
                name: 'type',
                description: 'Filter by notification type',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'unreadOnly',
                description: 'Filter by unread status',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'boolean')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Notifications list',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: new Model(type: NotificationResponse::class))
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Not authenticated'),
        ]
    )]
    public function listNotifications(
        Request $request,
        #[CurrentUser] ?IdentityUser $user
    ): JsonResponse {
        if (!$user) {
            return $this->json([
                'success' => false,
                'error' => 'Not authenticated',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $type = $request->query->get('type');
        $unreadOnly = $request->query->getBoolean('unreadOnly', false);
        $notifications = $this->notificationService->getRecent($user->getIdentity(), type: $type, unreadOnly: $unreadOnly);

        return $this->json([
            'success' => true,
            'data' => array_map(fn($n) => NotificationResponse::fromEntity($n), $notifications),
        ]);
    }

    #[Route('/unread', name: 'unread', methods: ['GET'])]
    #[OA\Get(
        description: 'Returns a list of unread notifications for the current user',
        summary: 'List unread notifications',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Unread notifications list',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: new Model(type: NotificationResponse::class))
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Not authenticated'),
        ]
    )]
    public function unreadNotifications(
        #[CurrentUser] ?IdentityUser $user
    ): JsonResponse {
        if (!$user) {
            return $this->json([
                'success' => false,
                'error' => 'Not authenticated',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $notifications = $this->notificationService->getUnread($user->getIdentity());

        return $this->json([
            'success' => true,
            'data' => array_map(fn($n) => NotificationResponse::fromEntity($n), $notifications),
        ]);
    }

    #[Route('/unread-count', name: 'unread_count', methods: ['GET'])]
    #[OA\Get(
        description: 'Returns the count of unread notifications for the current user',
        summary: 'Get unread count',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Unread count',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'count', type: 'integer', example: 5),
                            ],
                            type: 'object'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Not authenticated'),
        ]
    )]
    public function unreadCount(
        #[CurrentUser] ?IdentityUser $user
    ): JsonResponse {
        if (!$user) {
            return $this->json([
                'success' => false,
                'error' => 'Not authenticated',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $count = $this->notificationService->countUnread($user->getIdentity());

        return $this->json([
            'success' => true,
            'data' => compact('count'),
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    #[OA\Get(
        description: 'Returns details of a specific notification',
        summary: 'Get notification details',
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Notification ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Notification details',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', ref: new Model(type: NotificationResponse::class), type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Not authenticated'),
            new OA\Response(response: 403, description: 'Access denied'),
            new OA\Response(response: 404, description: 'Notification not found'),
        ]
    )]
    public function showNotification(
        string $id,
        #[CurrentUser] ?IdentityUser $user
    ): JsonResponse {
        if (!$user) {
            return $this->json([
                'success' => false,
                'error' => 'Not authenticated',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $notification = $this->notificationService->getById($id);

        if (!$notification) {
            return $this->json([
                'success' => false,
                'error' => 'Notification not found',
            ], Response::HTTP_NOT_FOUND);
        }

        // Check ownership
        if ($notification->getUser()->getUserId() !== $user->getIdentity()->getUserId()) {
            return $this->json([
                'success' => false,
                'error' => 'Access denied',
            ], Response::HTTP_FORBIDDEN);
        }

        return $this->json([
            'success' => true,
            'data' => NotificationResponse::fromEntity($notification),
        ]);
    }

    #[Route('/{id}/read', name: 'mark_read', methods: ['POST'])]
    #[OA\Post(
        description: 'Marks a specific notification as read',
        summary: 'Mark notification as read',
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Notification ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Notification marked as read',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Notification marked as read'),
                        new OA\Property(property: 'data', ref: new Model(type: NotificationResponse::class), type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Not authenticated'),
            new OA\Response(response: 403, description: 'Access denied'),
            new OA\Response(response: 404, description: 'Notification not found'),
        ]
    )]
    public function markAsRead(
        string $id,
        #[CurrentUser] ?IdentityUser $user
    ): JsonResponse {
        if (!$user) {
            return $this->json([
                'success' => false,
                'error' => 'Not authenticated',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $notification = $this->notificationService->getById($id);

        if (!$notification) {
            return $this->json([
                'success' => false,
                'error' => 'Notification not found',
            ], Response::HTTP_NOT_FOUND);
        }

        // Check ownership
        if ($notification->getUser()->getUserId() !== $user->getIdentity()->getUserId()) {
            return $this->json([
                'success' => false,
                'error' => 'Access denied',
            ], Response::HTTP_FORBIDDEN);
        }

        $this->notificationService->markAsRead($notification);

        return $this->json([
            'success' => true,
            'message' => 'Notification marked as read',
            'data' => NotificationResponse::fromEntity($notification),
        ]);
    }

    #[Route('/read-all', name: 'mark_all_read', methods: ['POST'], priority: 10)]
    #[OA\Post(
        description: 'Marks all unread notifications as read for the current user',
        summary: 'Mark all as read',
        responses: [
            new OA\Response(
                response: 200,
                description: 'All notifications marked as read',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Marked 5 notifications as read'),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'count', type: 'integer', example: 5),
                            ],
                            type: 'object'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Not authenticated'),
        ]
    )]
    public function markAllAsRead(
        #[CurrentUser] ?IdentityUser $user
    ): JsonResponse {
        if (!$user) {
            return $this->json([
                'success' => false,
                'error' => 'Not authenticated',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $count = $this->notificationService->markAllAsRead($user->getIdentity());

        return $this->json([
            'success' => true,
            'message' => "Marked $count notifications as read",
            'data' => compact('count'),
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[OA\Delete(
        description: 'Deletes a specific notification',
        summary: 'Delete notification',
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Notification ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Notification deleted',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Notification deleted'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Not authenticated'),
            new OA\Response(response: 403, description: 'Access denied'),
            new OA\Response(response: 404, description: 'Notification not found'),
        ]
    )]
    public function deleteNotification(
        string $id,
        #[CurrentUser] ?IdentityUser $user
    ): JsonResponse {
        if (!$user) {
            return $this->json([
                'success' => false,
                'error' => 'Not authenticated',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $notification = $this->notificationService->getById($id);

        if (!$notification) {
            return $this->json([
                'success' => false,
                'error' => 'Notification not found',
            ], Response::HTTP_NOT_FOUND);
        }

        // Check ownership
        if ($notification->getUser()->getUserId() !== $user->getIdentity()->getUserId()) {
            return $this->json([
                'success' => false,
                'error' => 'Access denied',
            ], Response::HTTP_FORBIDDEN);
        }

        $this->notificationService->delete($notification);

        return $this->json([
            'success' => true,
            'message' => 'Notification deleted',
        ]);
    }
}
