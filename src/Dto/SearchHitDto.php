<?php

namespace Olekjs\Elasticsearch\Dto;

use Illuminate\Contracts\Support\Arrayable;
use Olekjs\Elasticsearch\Contracts\ResponseDtoInterface;

class SearchHitDto implements ResponseDtoInterface, Arrayable
{
    public function __construct(
        private readonly string $index,
        private readonly string $id,
        private readonly float $score,
        private readonly array $source,
    ) {
    }

    public function getIndex(): string
    {
        return $this->index;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getScore(): float
    {
        return $this->score;
    }

    public function getSource(): array
    {
        return $this->source;
    }

    public function toArray(): array
    {
        return [
            'index' => $this->getIndex(),
            'id' => $this->getId(),
            'score' => $this->getScore(),
            'source' => $this->getSource(),
        ];
    }
}
