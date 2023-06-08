<?php

namespace Olekjs\Elasticsearch\Dto;

use Illuminate\Contracts\Support\Arrayable;
use Olekjs\Elasticsearch\Contracts\ResponseDtoInterface;

class ShardsResponseDto implements ResponseDtoInterface, Arrayable
{
    public function __construct(
        private readonly int $total,
        private readonly int $successful,
        private readonly int $failed,
        private readonly ?int $skipped = null,
    ) {
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getSuccessful(): int
    {
        return $this->successful;
    }

    public function getFailed(): int
    {
        return $this->failed;
    }

    public function getSkipped(): ?int
    {
        return $this->skipped;
    }

    public function toArray(): array
    {
        return [
            'total' => $this->getTotal(),
            'successful' => $this->getSuccessful(),
            'failed' => $this->getFailed(),
            'skipped' => $this->getSkipped(),
        ];
    }
}
