<?php

namespace Olekjs\Elasticsearch\Dto;

use Illuminate\Contracts\Support\Arrayable;
use Olekjs\Elasticsearch\Contracts\ResponseDtoInterface;

class SearchResponseDto implements ResponseDtoInterface, Arrayable
{
    public function __construct(
        private readonly int $took,
        private readonly bool $isTimedOut,
        private readonly ShardsResponseDto $shards,
        private readonly SearchHitsDto $results,
    ) {
    }

    public function getTook(): int
    {
        return $this->took;
    }

    public function getIsTimedOut(): bool
    {
        return $this->isTimedOut;
    }

    public function getResults(): SearchHitsDto
    {
        return $this->results;
    }

    public function getShards(): ShardsResponseDto
    {
        return $this->shards;
    }

    public function toArray(): array
    {
        return [
            'took' => $this->getTook(),
            'is_timed_out' => $this->getIsTimedOut(),
            'shards' => $this->getShards()->toArray(),
            'results' => $this->getResults()->toArray(),
        ];
    }
}
