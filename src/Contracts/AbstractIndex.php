<?php

namespace Olekjs\Elasticsearch\Contracts;

use Olekjs\Elasticsearch\Builder\Builder;

abstract class AbstractIndex
{
    protected string $index;

    public function getIndexName(): string
    {
        return $this->index;
    }

    public static function query(): Builder
    {
        $index = (new static)->getIndexName();

        return Builder::query()->index($index);
    }
}
