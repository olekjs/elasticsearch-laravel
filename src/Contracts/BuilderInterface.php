<?php

namespace Olekjs\Elasticsearch\Contracts;

use Olekjs\Elasticsearch\Builder\Builder;
use Olekjs\Elasticsearch\Dto\FindResponseDto;
use Olekjs\Elasticsearch\Dto\PaginateResponseDto;
use Olekjs\Elasticsearch\Dto\SearchResponseDto;
use Olekjs\Elasticsearch\Exceptions\CoreException;
use Olekjs\Elasticsearch\Exceptions\FindResponseException;
use Olekjs\Elasticsearch\Exceptions\IndexNotFoundResponseException;
use Olekjs\Elasticsearch\Exceptions\NotFoundResponseException;
use Olekjs\Elasticsearch\Exceptions\SearchResponseException;

interface BuilderInterface
{
    public static function query(?ClientInterface $client = null): Builder;

    public function index(string $index): self;

    public function where(string $field, string $value): self;

    public function orWhere(string $field, string $value): self;

    public function whereLike(string $field, string|int|float|array $value): self;

    public function orWhereLike(string $field, string|int|float|array $value): self;

    public function whereIn(string $field, array $values): self;

    public function orWhereIn(string $field, array $values): self;

    public function whereGreaterThan(string $field, int|float $value): self;

    public function whereLessThan(string $field, int|float $value): self;

    public function whereBetween(string $field, array $values): self;

    public function whereRange(string $field, int|float $value, string $operator): self;

    public function offset(int $offset): self;

    public function limit(int $limit): self;

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

    public function getIndex(): string;

    public function getBody(): array;

    public function getQuery(): array;
}
