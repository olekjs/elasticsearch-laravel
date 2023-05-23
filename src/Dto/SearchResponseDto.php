<?php

namespace Olekjs\Elasticsearch\Dto;

use Olekjs\Elasticsearch\Contracts\ResponseDtoInterface;

class SearchResponseDto implements ResponseDtoInterface
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
}
