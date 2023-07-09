<?php

namespace Olekjs\Elasticsearch\Contracts;

use LogicException;
use Olekjs\Elasticsearch\Builder\Builder;
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

interface BuilderInterface
{
    public const ORDER_DESC = 'desc';

    public static function query(?ClientInterface $client = null): Builder;

    public function index(string $index): self;

    public function whereKeyword(string $field, string $value): self;

    public function orWhereKeyword(string $field, string $value): self;

    public function where(string $field, string|int|float|array $value): self;

    public function orWhere(string $field, string|int|float|array $value): self;

    public function whereIn(string $field, array $values): self;

    public function orWhereIn(string $field, array $values): self;

    public function whereLike(string $field, string|int|float|array $value): self;

    public function orWhereLike(string $field, string|int|float|array $value): self;

    public function whereNot(string $field, string|int|float|array $value): self;

    public function orWhereNot(string $field, string|int|float|array $value): self;

    public function whereGreaterThan(string $field, int|float $value): self;

    public function whereLessThan(string $field, int|float $value): self;

    public function whereBetween(string $field, array $values): self;

    public function whereRange(string $field, int|float $value, string $operator): self;

    /**
     * @throws LogicException
     */
    public function orderBy(string $field, string $direction = self::ORDER_DESC, ?string $mode = null): self;

    public function offset(int $offset): self;

    public function limit(int $limit): self;

    public function rawQuery(array $query): self;

    public function rawSort(array $sort): self;

    public function select(mixed $fields): self;

    /**
     * @throws IndexNotFoundResponseException
     * @throws FindResponseException
     */
    public function find(int|string $id): ?FindResponseDto;

    /**
     * @throws IndexNotFoundResponseException
     * @throws NotFoundResponseException
     * @throws FindResponseException
     */
    public function findOrFail(int|string $id): FindResponseDto;

    /**
     * @throws SearchResponseException
     * @throws CoreException
     */
    public function count(): int;

    /**
     * @throws SearchResponseException
     */
    public function get(): SearchResponseDto;

    /**
     * @throws SearchResponseException
     * @throws CoreException
     */
    public function paginate(int $page = 1, int $perPage = 25): PaginateResponseDto;

    /**
     * @throws SearchResponseException
     * @throws CoreException
     */
    public function bulk(BulkOperationInterface $bulk): BulkResponseDto;

    /**
     * @throws IndexResponseException
     */
    public function create(string|int $id, array $data): IndexResponseDto;

    /**
     * @throws NotFoundResponseException
     * @throws UpdateResponseException
     * @throws ConflictResponseException
     */
    public function update(
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
    public function delete(string|int $id): IndexResponseDto;

    public function getIndex(): string;

    public function getBody(): array;

    public function getQuery(): array;

    public function getSort(): array;

    public function getSelect(): array;

    public function performSearchBody(): void;
}
