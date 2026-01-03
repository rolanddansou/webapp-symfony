<?php

namespace App\Feature\Activity\Service;

use App\Entity\Access\IdentityInterface;
use App\Entity\Activity\UserActivity;
use App\Feature\Activity\ActivityRecorder;
use App\Feature\Activity\DTO\ActivityListResponse;
use App\Feature\Activity\DTO\ActivityResponse;
use App\Repository\Activity\UserActivityRepository;

final class ActivityService
{
    public function __construct(
        private readonly UserActivityRepository $activityRepository,
        private readonly ActivityRecorder $activityRecorder,
    ) {}

    public function getForUser(
        IdentityInterface $user,
        int $page = 1,
        int $limit = 20,
        ?string $type = null,
        ?\DateTimeImmutable $from = null,
        ?\DateTimeImmutable $to = null
    ): ActivityListResponse {
        $userId = $user->getUserId();
        
        $activities = $this->activityRepository->findByUserPaginated(
            $userId,
            $page,
            $limit,
            $type,
            $from,
            $to
        );
        
        $total = $this->activityRepository->countByUser($userId, $type, $from, $to);

        $items = array_map(
            fn(UserActivity $activity) => ActivityResponse::fromEntity($activity),
            $activities
        );

        return ActivityListResponse::create($items, $total, $page, $limit);
    }

    public function getById(string $id): ?UserActivity
    {
        return $this->activityRepository->find($id);
    }

    public function record(
        IdentityInterface $user,
        string $type,
        array $payload = [],
        ?string $actorId = null,
        ?string $actorType = null
    ): void {
        $this->activityRecorder->record(
            $user->getUserId(),
            $type,
            $payload,
            $actorId,
            $actorType
        );
    }
}
