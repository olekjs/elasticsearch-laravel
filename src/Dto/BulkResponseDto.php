<?php

namespace Olekjs\Elasticsearch\Dto;

use Illuminate\Contracts\Support\Arrayable;
use Olekjs\Elasticsearch\Contracts\ResponseDtoInterface;

class BulkResponseDto implements ResponseDtoInterface, Arrayable
{
    public function __construct(
        private readonly int $took,
        private readonly bool $errors,
        private readonly array $items,
    ) {
    }

    public function getTook(): int
    {
        return $this->took;
    }

    public function isErrors(): bool
    {
        return $this->errors;
    }

    /**
     * @return BulkItemResponseDto[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function toArray(): array
    {
        return [
            'took' => $this->getTook(),
            'errors' => $this->isErrors(),
            'items' => array_map(
                fn(BulkItemResponseDto $bulkItemResponseDto): array => $bulkItemResponseDto->toArray(),
                $this->getItems()
            ),
        ];
    }
}
