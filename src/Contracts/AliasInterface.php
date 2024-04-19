<?php

namespace Olekjs\Elasticsearch\Contracts;

use Illuminate\Http\Client\Response;

interface AliasInterface
{
    /**
     * @return array<int, string>
     */
    public function getIndicesForAlias(string $alias): array;

    public function add(string $index, string $alias): bool;

    public function remove(string $index, string $alias): bool;

    public function runActions(array $actions): Response;

    public function replace(string $alias, string $newIndex, ?string $oldIndex = null): bool;
}