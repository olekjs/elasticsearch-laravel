<?php

namespace Elasticsearch\Dto;

class SearchResponseDto
{
    public function __construct(
        private readonly int $took,
        private readonly bool $isTimedOut,
        private readonly ShardsResponseDto $shards,
        private readonly SearchHitsDto $hits,
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

    public function getHits(): SearchHitsDto
    {
        return $this->hits;
    }

    public function getShards(): ShardsResponseDto
    {
        return $this->shards;
    }
}
