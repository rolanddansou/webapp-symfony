<?php

namespace App\Feature\Activity;

use App\Entity\Activity\UserActivity;

readonly class ActivityRecorder
{
    public function __construct(
        private ActivityRepositoryInterface $repo,
    ) {}

    public function record(string $userId, string $type, array $payload = [], ?string $actorId = null, ?string $actorType = null, bool $async = false): void
    {
        $activity = new UserActivity($userId, $type, $payload, $actorId, $actorType);

        if ($async) {
            /// TODO: Dispatch to message bus
            return;
        }

        $this->repo->add($activity);
    }
}
