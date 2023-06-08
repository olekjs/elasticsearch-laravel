<?php

namespace Olekjs\Elasticsearch\Dto;

use Illuminate\Contracts\Support\Arrayable;
use Olekjs\Elasticsearch\Contracts\ResponseDtoInterface;

class SearchHitsDto implements ResponseDtoInterface, Arrayable
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

    public function toArray(): array
    {
        return [
            'total' => $this->getTotal(),
            'max_score' => $this->getMaxScore(),
            'hits' => array_map(
                fn(SearchHitDto $searchHitDto) => $searchHitDto->toArray(),
                $this->getHits()
            ),
        ];
    }
}
