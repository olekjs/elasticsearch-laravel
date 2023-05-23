<?php

namespace Elasticsearch\Dto;

class SearchHitsDto
{
    public function __construct(
        private readonly array $total,
        private readonly float $maxScore,

        /** @var SearchHitDto[] */
        private readonly array $hits = [],
    ) {
    }

    public function getTotal(): array
    {
        return $this->total;
    }

    public function getMaxScore(): float
    {
        return $this->maxScore;
    }

    /** @return SearchHitDto[] */
    public function getHits(): array
    {
        return $this->hits;
    }
}
