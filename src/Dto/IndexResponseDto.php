<?php

namespace Olekjs\Elasticsearch\Dto;

use Illuminate\Contracts\Support\Arrayable;
use Olekjs\Elasticsearch\Contracts\ResponseDtoInterface;

class IndexResponseDto implements ResponseDtoInterface, Arrayable
{
    public function __construct(
        private readonly string $index,
        private readonly string $id,
        private readonly int $version,
        private readonly string $result,
        private readonly ShardsResponseDto $shards,
        private readonly int $sequenceNumber,
        private readonly int $primaryTerm,
    ) {
    }

    public function getIndex(): ?string
    {
        return $this->index;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getResult(): ?string
    {
        return $this->result;
    }

    public function getShards(): ShardsResponseDto
    {
        return $this->shards;
    }

    public function getSequenceNumber(): int
    {
        return $this->sequenceNumber;
    }

    public function getPrimaryTerm(): int
    {
        return $this->primaryTerm;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function toArray(): array
    {
        return [
            'index' => $this->getIndex(),
            'id' => $this->getId(),
            'version' => $this->getVersion(),
            'result' => $this->getResult(),
            'shards' => $this->getShards()->toArray(),
            'sequence_number' => $this->getSequenceNumber(),
            'primary_term' => $this->getPrimaryTerm(),
        ];
    }
}
