<?php

namespace Olekjs\Elasticsearch\Dto;

use Illuminate\Contracts\Support\Arrayable;
use Olekjs\Elasticsearch\Contracts\ResponseDtoInterface;

class BulkItemResponseDto implements ResponseDtoInterface, Arrayable
{
    public function __construct(
        private readonly string $action,
        private readonly IndexResponseDto $data,
    ) {
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getData(): IndexResponseDto
    {
        return $this->data;
    }

    public function toArray(): array
    {
        return [
            'took' => $this->getAction(),
            'items' => $this->getData()->toArray(),
        ];
    }
}
