<?php

namespace Olekjs\Elasticsearch\Builder;

use Illuminate\Support\Traits\Conditionable;
use LogicException;
use Olekjs\Elasticsearch\Client;
use Olekjs\Elasticsearch\Contracts\BuilderInterface;
use Olekjs\Elasticsearch\Contracts\ClientInterface;
use Olekjs\Elasticsearch\Dto\FindResponseDto;
use Olekjs\Elasticsearch\Dto\PaginateResponseDto;
use Olekjs\Elasticsearch\Dto\SearchResponseDto;
use Olekjs\Elasticsearch\Exceptions\CoreException;
use Olekjs\Elasticsearch\Exceptions\FindResponseException;
use Olekjs\Elasticsearch\Exceptions\IndexNotFoundResponseException;
use Olekjs\Elasticsearch\Exceptions\NotFoundResponseException;
use Olekjs\Elasticsearch\Exceptions\SearchResponseException;

class Builder implements BuilderInterface
{
    use Conditionable;

    private string $index;

    private array $query;

    private array $body = [];

    public function __construct(private readonly ClientInterface $client)
    {
    }

    public static function query(?ClientInterface $client = null): Builder
    {
        if (is_null($client)) {
            $client = new Client();
        }

        return new Builder($client);
    }

    public function index(string $index): self
    {
        $this->index = $index;

        return $this;
    }

    public function where(string $field, string $value): self
    {
        $this->query['bool']['filter'][]['term'][$field . '.keyword'] = $value;

        return $this;
    }

    public function orWhere(string $field, string $value): self
    {
        $this->query['bool']['should'][]['term'][$field . '.keyword'] = $value;

        return $this;
    }

    public function whereLike(string $field, string|int|float|array $value): self
    {
        $this->query['bool']['filter'][]['term'][$field] = $value;

        return $this;
    }

    public function orWhereLike(string $field, string|int|float|array $value): self
    {
        $this->query['bool']['should'][]['term'][$field] = $value;

        return $this;
    }

    public function whereIn(string $field, array $values): self
    {
        $this->query['bool']['filter'][]['terms'][$field] = $values;

        return $this;
    }

    public function orWhereIn(string $field, array $values): self
    {
        $this->query['bool']['should'][]['terms'][$field] = $values;

        return $this;
    }

    public function whereGreaterThan(string $field, int|float $value): self
    {
        return $this->whereRange($field, $value, 'gt');
    }

    public function whereLessThan(string $field, int|float $value): self
    {
        return $this->whereRange($field, $value, 'lt');
    }

    public function whereBetween(string $field, array $values): self
    {
        if (!isset($values[0], $values[1])) {
            throw new LogicException('Provide two values');
        }

        $this->whereRange($field, $values[0], 'gte');
        $this->whereRange($field, $values[1], 'lte');

        return $this;
    }

    public function whereRange(string $field, int|float $value, string $operator): self
    {
        $this->query['bool']['filter'][]['range'][$field][$operator] = $value;

        return $this;
    }

    public function offset(int $offset): self
    {
        $this->body['from'] = $offset;

        return $this;
    }

    public function limit(int $limit): self
    {
        $this->body['size'] = $limit;

        return $this;
    }

    /**
     * @throws IndexNotFoundResponseException
     * @throws FindResponseException
     */
    public function find(int|string $id): ?FindResponseDto
    {
        if (!isset($this->index)) {
            throw new LogicException('Index name is required.');
        }

        return $this->client->find($this->index, $id);
    }

    /**
     * @throws IndexNotFoundResponseException
     * @throws NotFoundResponseException
     * @throws FindResponseException
     */
    public function findOrFail(int|string $id): FindResponseDto
    {
        if (!isset($this->index)) {
            throw new LogicException('Index name is required.');
        }

        return $this->client->findOrFail($this->index, $id);
    }

    /**
     * @throws SearchResponseException
     * @throws CoreException
     */
    public function count(): int
    {
        if (!isset($this->index)) {
            throw new LogicException('Index name is required.');
        }

        $this->performSearchBody();

        unset($this->body['size']);

        return $this->client->count($this->index, $this->body);
    }

    /**
     * @throws SearchResponseException
     */
    public function get(): SearchResponseDto
    {
        if (!isset($this->index)) {
            throw new LogicException('Index name is required.');
        }

        $this->performSearchBody();

        return $this->client->search($this->index, $this->body);
    }

    /**
     * @throws SearchResponseException
     * @throws CoreException
     */
    public function paginate(int $page = 1, int $perPage = 25): PaginateResponseDto
    {
        if (!isset($this->index)) {
            throw new LogicException('Index name is required.');
        }

        $this->performSearchBody();

        return $this->client->paginate($this->index, $this->body, $page, $perPage);
    }

    public function getIndex(): string
    {
        return $this->index;
    }

    public function getBody(): array
    {
        return $this->body;
    }

    public function getQuery(): array
    {
        return $this->query;
    }

    private function performSearchBody(): void
    {
        if (isset($this->query)) {
            $this->body['query'] = $this->query;
        }

        if (empty($this->body)) {
            $this->body['query'] = [
                'match_all' => (object)[]
            ];
        }
    }
}
