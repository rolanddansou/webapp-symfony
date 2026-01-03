<?php

namespace App\Feature\Activity;

use App\Entity\Activity\UserActivity;

interface ActivityRepositoryInterface
{
    public function add(UserActivity $activity): void;
    public function addBatch(array $activities): void; // bulk insert
}
