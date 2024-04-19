<?php

namespace Olekjs\Elasticsearch\Contracts;

interface IndexInterface
{
    public function create(string $name): bool;

    public function delete(string $name): bool;
}