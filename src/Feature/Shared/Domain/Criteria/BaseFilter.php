<?php

namespace App\Feature\Shared\Domain\Criteria;

abstract class BaseFilter
{
    public function __construct(
        public string|null $query= null,
        public int|null $page= 1,
        public int|null $limit= 30,
        public string|null $orderBy= null,
        public string|null $orderDirection= null,
        public bool|null $enabled= null,
    ){}

    public array $id= [];

    /**
     * @var callable|null
     */
    public $callback= null;

    public function page(): int
    {
        return $this->page ?? 1;
    }

    public function limit(): int
    {
        return $this->limit ?? 30;
    }

    public function orderBy(): string|null
    {
        return $this->orderBy;
    }

    public function orderDirection(): string|null
    {
        return $this->orderDirection;
    }

    public function query(): string|null
    {
        return $this->query;
    }

    /**
     * @param string|null $query
     */
    public function setQuery(?string $query): void
    {
        $this->query = $query;
    }

    public function enabled(): bool|null
    {
        return $this->enabled;
    }
}
