## Introduction
This is a simple package that is used to integrate the Elasticsearch API with a Laravel project.

_The package is still in development._

- [Installation](#installation)
- [Custom Indices](#custom-indices)
- [General usage](#general-usage)
- [Examples](#examples)
- [Client class reference](#client-class-reference)
- [Running Tests](#running-tests)

## Installation

Install using composer

```bash
  composer require olekjs/elasticsearch
```
    
Set Elasticsearch base URL in `.env` file

```txt
ELASTICSEARCH_URL=http://localhost:9200
```

## Custom Indices
Custom Indices work similarly to Laravel models.
First, you need to define your own class that extends AbstractIndex:

```php
<?php

namespace App\Indices;

use Olekjs\Elasticsearch\Contracts\AbstractIndex;

class LogIndex extends AbstractIndex
{
    protected string $index = 'logs';
}
```

You can then use Custom Index to perform a search operation without specifying an index name:

```php
use App\Indices\LogIndex;

...

LogIndex::query()->where('type', 'error')->get();
```

### General usage
There are three options for making requests:

1. Using builder
```php
use Olekjs\Elasticsearch\Builder\Builder;

...

$results = Builder::query()
    ->index('shops')
    ->where('slug', 'test-slug')
    ->where('email', 'test@test.com')
    ->whereLike('name', 'test')
    ->whereIn('_id', [123, 321])
    ->get(); // Resturns SearchResponseDto
```
2. Using Custom Index
How to use Custom Indices? See: Custom Indices
```php
use App\Indices\MyCustomIndex;

...

$results = MyCustomIndex::query()
    ->where('slug', 'test-slug')
    ->where('email', 'test@test.com')
    ->whereLike('name', 'test')
    ->whereIn('_id', [123, 321])
    ->get(); // Resturns SearchResponseDto
```

3. Directly using the Client class
```php
use Olekjs\Elasticsearch\Client;

...

$client = new Client();
$client->search('logs', [
  'query' => [
    'bool' => ['filter' => ['term' => ['email' => 'test@test.com']]]
  ]
]);
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

## Running Tests

To run tests, run the following command

```bash
  php vendor/bin/testbench package:test
```
