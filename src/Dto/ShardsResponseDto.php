<?php

namespace Elasticsearch\Dto;

class ShardsResponseDto
{
    public function __construct(
        private readonly int $total,
        private readonly int $successful,
        private readonly int $failed,
        private readonly ?int $skipped = null,
    ) {
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getSuccessful(): int
    {
        return $this->successful;
    }

    public function getFailed(): int
    {
        return $this->failed;
    }

    public function getSkipped(): ?int
    {
        return $this->skipped;
    }
}
