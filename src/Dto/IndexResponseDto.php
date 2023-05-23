<?php

namespace Elasticsearch\Dto;

class IndexResponseDto
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
}
