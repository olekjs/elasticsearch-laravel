<?php

namespace Olekjs\Elasticsearch\Contracts;

use Olekjs\Elasticsearch\Dto\FindResponseDto;
use Olekjs\Elasticsearch\Dto\IndexResponseDto;
use Olekjs\Elasticsearch\Dto\SearchResponseDto;

interface ClientInterface
{
    public function search(string $index, array $data): SearchResponseDto;

    public function find(string $index, string|int $id): ?FindResponseDto;

    public function findOrFail(string $index, string|int $id): FindResponseDto;

    public function create(string $index, string|int $id, array $data): IndexResponseDto;

    public function update(string $index, string|int $id, array $data): IndexResponseDto;

    public function delete(string $index, string|int $id): IndexResponseDto;

    public function searchWhereIn(string $index, string $field, array $values): SearchResponseDto;

    public function searchWhereKeyword(string $index, string $field, string $value): SearchResponseDto;

    public function searchWhereLike(string $index, string $field, string|int|float $value): SearchResponseDto;

    public function increment(string $index, string|int $id, string $field, int $value = 1): IndexResponseDto;

    public function decrement(string $index, string|int $id, string $field, int $value = 1): IndexResponseDto;
}
