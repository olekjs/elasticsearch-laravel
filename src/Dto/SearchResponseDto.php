<?php

namespace Olekjs\Elasticsearch\Dto;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Olekjs\Elasticsearch\Contracts\Collectionable;
use Olekjs\Elasticsearch\Contracts\ResponseDtoInterface;

class SearchResponseDto implements ResponseDtoInterface, Arrayable, Collectionable
{
    public function __construct(
        private readonly int $took,
        private readonly bool $isTimedOut,
        private readonly ShardsResponseDto $shards,
        private readonly SearchHitsDto $result,
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

    public function getResult(): SearchHitsDto
    {
        return $this->result;
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
            'results' => $this->getResult()->toArray(),
        ];
    }

    public function toCollect(bool $asArray = false): Collection
    {
        $hitsCollection = $this->getResult()->getHits();

        if ($asArray) {
            $hits = array_map(
                fn(SearchHitDto $searchHitDto) => $searchHitDto->toArray(),
                $hitsCollection
            );

            return Collection::make($hits);
        }

        return Collection::make($hitsCollection);
    }
}
