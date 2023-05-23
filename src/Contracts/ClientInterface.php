<?php

namespace Elasticsearch\Contracts;

use Elasticsearch\Dto\FindResponseDto;
use Elasticsearch\Dto\IndexResponseDto;
use Elasticsearch\Dto\SearchResponseDto;

interface ClientInterface
{
    // todo
    // whereIn
    // where per field
    // increment
    // decrement
    // update by query ??
    // bulk action

    public function search(string $index, array $data): SearchResponseDto;

    public function find(string $index, string|int $id): ?FindResponseDto;

    public function findOrFail(string $index, string|int $id): FindResponseDto;

    public function create(string $index, string|int $id, array $data): IndexResponseDto;

    public function update(string $index, string|int $id, array $data): IndexResponseDto;

    public function delete(string $index, string|int $id): IndexResponseDto;
}
