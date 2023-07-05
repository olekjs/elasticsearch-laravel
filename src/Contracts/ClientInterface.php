<?php

namespace Olekjs\Elasticsearch\Contracts;

use Olekjs\Elasticsearch\Dto\BulkResponseDto;
use Olekjs\Elasticsearch\Dto\FindResponseDto;
use Olekjs\Elasticsearch\Dto\IndexResponseDto;
use Olekjs\Elasticsearch\Dto\PaginateResponseDto;
use Olekjs\Elasticsearch\Dto\SearchResponseDto;
use Olekjs\Elasticsearch\Exceptions\ConflictResponseException;
use Olekjs\Elasticsearch\Exceptions\CoreException;
use Olekjs\Elasticsearch\Exceptions\DeleteResponseException;
use Olekjs\Elasticsearch\Exceptions\FindResponseException;
use Olekjs\Elasticsearch\Exceptions\IndexNotFoundResponseException;
use Olekjs\Elasticsearch\Exceptions\IndexResponseException;
use Olekjs\Elasticsearch\Exceptions\NotFoundResponseException;
use Olekjs\Elasticsearch\Exceptions\SearchResponseException;
use Olekjs\Elasticsearch\Exceptions\UpdateResponseException;

interface ClientInterface
{
    /**
     * @throws SearchResponseException
     */
    public function search(string $index, array $data): SearchResponseDto;

    /**
     * @throws IndexNotFoundResponseException
     * @throws FindResponseException
     */
    public function find(string $index, string|int $id): ?FindResponseDto;

    /**
     * @throws NotFoundResponseException
     * @throws IndexNotFoundResponseException
     * @throws FindResponseException
     */
    public function findOrFail(string $index, string|int $id): FindResponseDto;

    /**
     * @throws IndexResponseException
     */
    public function create(string $index, string|int $id, array $data): IndexResponseDto;

    /**
     * @throws NotFoundResponseException
     * @throws UpdateResponseException
     * @throws ConflictResponseException
     */
    public function update(
        string $index,
        string|int $id,
        array $data = [],
        array $script = [],
        ?int $primaryTerm = null,
        ?int $sequenceNumber = null
    ): IndexResponseDto;

    /**
     * @throws DeleteResponseException
     * @throws NotFoundResponseException
     */
    public function delete(string $index, string|int $id): IndexResponseDto;

    /**
     * @throws SearchResponseException
     */
    public function searchWhereIn(string $index, string $field, array $values): SearchResponseDto;

    /**
     * @throws SearchResponseException
     */
    public function searchWhereKeyword(string $index, string $field, string $value): SearchResponseDto;

    /**
     * @throws SearchResponseException
     */
    public function searchWhereLike(string $index, string $field, string|int|float $value): SearchResponseDto;

    /**
     * @throws NotFoundResponseException
     * @throws UpdateResponseException
     * @throws ConflictResponseException
     */
    public function increment(string $index, string|int $id, string $field, int $value = 1): IndexResponseDto;

    /**
     * @throws NotFoundResponseException
     * @throws UpdateResponseException
     * @throws ConflictResponseException
     */
    public function decrement(string $index, string|int $id, string $field, int $value = 1): IndexResponseDto;

    /**
     * @throws SearchResponseException
     * @throws CoreException
     */
    public function count(string $index, array $data = []): int;

    /**
     * @throws SearchResponseException
     * @throws CoreException
     */
    public function paginate(string $index, array $data = [], int $page = 1, int $perPage = 25): PaginateResponseDto;

    /**
     * @throws SearchResponseException
     * @throws CoreException
     */
    public function bulk(BulkOperationInterface $bulk): BulkResponseDto;
}
