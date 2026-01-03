<?php

namespace App\Feature\Notification\DTO;

final class UnreadCountResponse
{
    public function __construct(
        public readonly int $count,
    ) {}
}
