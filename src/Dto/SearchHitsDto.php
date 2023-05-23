<?php

namespace Olekjs\Elasticsearch\Dto;

use Olekjs\Elasticsearch\Contracts\ResponseDtoInterface;

class SearchHitsDto implements ResponseDtoInterface
{
    public function __construct(
        private readonly array $total,
        private readonly ?float $maxScore = null,

        /** @var SearchHitDto[] */
        private readonly array $hits = [],
    ) {
    }

    public function getTotal(): array
    {
        return $this->total;
    }

    public function getMaxScore(): ?float
    {
        return $this->maxScore;
    }

    /** @return SearchHitDto[] */
    public function getHits(): array
    {
        return $this->hits;
    }
}
