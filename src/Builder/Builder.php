<?php

namespace Olekjs\Elasticsearch\Builder;

use Illuminate\Support\Traits\Conditionable;
use LogicException;
use Olekjs\Elasticsearch\Client;
use Olekjs\Elasticsearch\Contracts\BuilderInterface;
use Olekjs\Elasticsearch\Contracts\BulkOperationInterface;
use Olekjs\Elasticsearch\Contracts\ClientInterface;
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

class Builder implements BuilderInterface
{
    use Conditionable;

    public const ORDER_DESC = 'desc';

    public const ORDER_ASC = 'asc';

    public const ORDER_MIN_MODE = 'min';

    public const ORDER_MAX_MODE = 'max';

    public const ORDER_SUM_MODE = 'sum';

    public const ORDER_AVG_MODE = 'avg';

    public const ORDER_MEDIAN_MODE = 'median';

    private string $index;

    private array $query;

    private array $sort;

    private array $select;

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

    public function whereKeyword(string $field, string $value): self
    {
        $this->query['bool']['filter'][]['term'][$field . '.keyword'] = $value;

        return $this;
    }

    public function orWhereKeyword(string $field, string $value): self
    {
        $this->query['bool']['should'][]['term'][$field . '.keyword'] = $value;

        return $this;
    }

    public function where(string $field, string|int|float|array $value): self
    {
        $this->query['bool']['filter'][]['term'][$field] = $value;

        return $this;
    }

    public function orWhere(string $field, string|int|float|array $value): self
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

    public function whereLike(string $field, string|int|float|array $value): self
    {
        $this->query['bool']['filter'][]['wildcard'][$field] = $value;

        return $this;
    }

    public function orWhereLike(string $field, string|int|float|array $value): self
    {
        $this->query['bool']['should'][]['wildcard'][$field] = $value;

        return $this;
    }

    public function whereNot(string $field, string|int|float|array $value): self
    {
        $this->query['bool']['must_not'][]['term'][$field] = $value;

        return $this;
    }

    public function orWhereNot(string $field, string|int|float|array $value): self
    {
        $this->query['bool']['should'][]['bool']['must_not'][]['term'][$field] = $value;

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

    /**
     * @throws LogicException
     */
    public function orderBy(string $field, string $direction = self::ORDER_DESC, ?string $mode = null): self
    {
        if ($direction !== self::ORDER_DESC && $direction !== self::ORDER_ASC) {
            throw new LogicException(
                sprintf(
                    'Available direction values [%s, %s]. Entered value: [%s]',
                    self::ORDER_DESC,
                    self::ORDER_ASC,
                    $direction,
                )
            );
        }

        $order = match (true) {
            !is_null($mode) => function () use ($mode, $field, $direction): void {
                $availableValues = [
                    self::ORDER_MIN_MODE,
                    self::ORDER_MAX_MODE,
                    self::ORDER_SUM_MODE,
                    self::ORDER_AVG_MODE,
                    self::ORDER_MEDIAN_MODE,
                ];

                if (!in_array($mode, $availableValues)) {
                    throw new LogicException(
                        sprintf(
                            'Available direction values [%s, %s, %s, %s, %s]. Entered value: [%s]',
                            self::ORDER_MIN_MODE,
                            self::ORDER_MAX_MODE,
                            self::ORDER_SUM_MODE,
                            self::ORDER_AVG_MODE,
                            self::ORDER_MEDIAN_MODE,
                            $mode,
                        )
                    );
                }

                $this->sort[][$field] = ['order' => $direction, 'mode' => $mode];
            },
            is_null($mode) => function () use ($field, $direction): void {
                $this->sort[][$field] = $direction;
            }
        };

        $order();

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

    public function rawQuery(array $query): self
    {
        $this->query = $query;

        return $this;
    }

    public function rawSort(array $sort): self
    {
        $this->sort = $sort;

        return $this;
    }

    public function select(mixed $fields): self
    {
        $fields = is_array($fields) ? $fields : func_get_args();

        foreach ($fields as $field) {
            $this->select[] = $field;
        }

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

    /**
     * @throws SearchResponseException
     * @throws CoreException
     */
    public function bulk(BulkOperationInterface $bulk): BulkResponseDto
    {
        return $this->client->bulk($bulk);
    }

    /**
     * @throws IndexResponseException
     */
    public function create(string|int $id, array $data): IndexResponseDto
    {
        if (!isset($this->index)) {
            throw new LogicException('Index name is required.');
        }

        return $this->client->create($this->index, $id, $data);
    }

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
    ): IndexResponseDto {
        if (!isset($this->index)) {
            throw new LogicException('Index name is required.');
        }

        return $this->client->update($this->index, $id, $data, $script, $primaryTerm, $sequenceNumber);
    }

    /**
     * @throws DeleteResponseException
     * @throws NotFoundResponseException
     */
    public function delete(string|int $id): IndexResponseDto
    {
        if (!isset($this->index)) {
            throw new LogicException('Index name is required.');
        }

        return $this->client->delete($this->index, $id);
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

    public function getSort(): array
    {
        return $this->sort;
    }

    public function getSelect(): array
    {
        return $this->select;
    }

    public function performSearchBody(): void
    {
        if (isset($this->query)) {
            $this->body['query'] = $this->query;
        }

        if (isset($this->select)) {
            $this->body['_source'] = $this->select;
        }

        if (empty($this->body)) {
            $this->body['query'] = [
                'match_all' => (object)[]
            ];
        }
    }
}
