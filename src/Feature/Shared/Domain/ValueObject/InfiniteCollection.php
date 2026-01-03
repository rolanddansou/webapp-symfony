<?php

namespace App\Feature\Shared\Domain\ValueObject;

use App\Feature\Shared\Domain\Criteria\Assert;

abstract class InfiniteCollection extends Collection implements \JsonSerializable
{
    public function __construct(
        public readonly array $items,
        public readonly int $totalItems,
        public readonly int|null $perPage,
        public readonly int|null $currentPage= 1,
        public readonly String|null $nextPageUrl= null,
        public readonly String|null $prevPageUrl= null,
        protected $callback= null
    ){
        Assert::arrayOf($this->type(), $items);
        parent::__construct($items);
    }

    public function totalItems(): int
    {
        return $this->totalItems;
    }

    public function perPage(): int
    {
        return $this->perPage;
    }

    public function currentPage(): int
    {
        return $this->currentPage ?? 1;
    }

    public function offset(): int
    {
        return ($this->currentPage() - 1) * $this->perPage();
    }

    public function first(): mixed
    {
        if($this->isEmpty())
            return null;

        return $this->items[0];
    }

    public function hasMore(): bool
    {
        return $this->nextPageUrl !== null;
    }

    public function jsonSerialize(): mixed
    {
        $items= $this->items;

        if($this->callback && is_callable($this->callback)){
            $items= array_map($this->callback, $items);
        }

        return [
            'items' => $items,
            'totalItems' => $this->totalItems(),
            'from' => $this->offset(),
            "to" => $this->offset() + $this->count(),
            "nextPageUrl" => $this->nextPageUrl,
            "prevPageUrl" => $this->prevPageUrl,
        ];
    }
}
