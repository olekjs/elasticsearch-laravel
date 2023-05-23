<?php

namespace Elasticsearch\Dto;

class FindResponseDto
{
    public function __construct(
        private readonly string $index,
        private readonly string $id,
        private readonly int $version,
        private readonly int $sequenceNumber,
        private readonly int $primaryTerm,
        private readonly bool $found,
        private readonly array $source,
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

    public function getVersion(): int
    {
        return $this->version;
    }

    public function getPrimaryTerm(): int
    {
        return $this->primaryTerm;
    }

    public function getSequenceNumber(): int
    {
        return $this->sequenceNumber;
    }

    public function getSource(): array
    {
        return $this->source;
    }

    public function isFound(): bool
    {
        return $this->found;
    }
}
