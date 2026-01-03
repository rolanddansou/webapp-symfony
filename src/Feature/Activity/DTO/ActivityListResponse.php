<?php

namespace App\Feature\Activity\DTO;

final readonly class ActivityListResponse
{
    /**
     * @param ActivityResponse[] $items
     */
    public function __construct(
        /** @var ActivityResponse[] */
        public array $items,
        public int   $total,
        public int   $page,
        public int   $limit,
        public int   $totalPages,
    ) {}

    /**
     * @param ActivityResponse[] $items
     */
    public static function create(array $items, int $total, int $page, int $limit): self
    {
        return new self(
            items: $items,
            total: $total,
            page: $page,
            limit: $limit,
            totalPages: $limit > 0 ? (int) ceil($total / $limit) : 0,
        );
    }
}
