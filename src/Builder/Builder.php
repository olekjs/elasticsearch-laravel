<?php

namespace Olekjs\Elasticsearch\Builder;

use Illuminate\Support\Traits\Conditionable;
use LogicException;
use Olekjs\Elasticsearch\Client;
use Olekjs\Elasticsearch\Contracts\ClientInterface;
use Olekjs\Elasticsearch\Dto\SearchResponseDto;

class Builder
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

    public function whereLike(string $field, string|int|float|array $value): self
    {
        $this->query['bool']['filter'][]['term'][$field] = $value;

        return $this;
    }

    public function whereIn(string $field, array $values): self
    {
        $this->query['bool']['filter'][]['terms'][$field] = $values;

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

    public function get(): SearchResponseDto
    {
        if (!isset($this->index)) {
            throw new LogicException('Index name is required.');
        }

        if (isset($this->query)) {
            $this->body['query'] = $this->query;
        }

        if (empty($this->body)) {
            $this->body['query'] = [
                'match_all' => (object)[]
            ];
        }

        return $this->client->search($this->index, $this->body);
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
}
