## Introduction
This is a simple package that is used to integrate the Elasticsearch API with a Laravel project.
The package is based on the Client class, which is not a builder.
The package is still in development.

## Installation

Install using composer

```bash
  composer require ...
```
    
Set Elasticsearch base URL in `.env` file

```txt
ELASTICSEARCH_URL=http://localhost:9200
```


## Client class reference

```php
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
```

### Examples
1. Index the new document
```php
use Olekjs\Elasticsearch\Client;

...

$client = new Client();
$client->create(
  index: 'logs',
  id: 1,
  data: ['level' => 'error', 'message' => 'Error...', ...]
);
```

2. Find the document
```php
use Olekjs\Elasticsearch\Client;

...

$client = new Client();

// If document doesn't exists null will be returned
$client->find(
  index: 'logs',
  id: 1
);

// If document doesn't exists NotFoundResponseException will be thrown
$client->findOrFail(
  index: 'logs',
  id: 2
);

```
