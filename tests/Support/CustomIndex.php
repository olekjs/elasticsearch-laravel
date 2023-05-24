<?php

namespace Olekjs\Elasticsearch\Tests\Support;

use Olekjs\Elasticsearch\Contracts\AbstractIndex;

class CustomIndex extends AbstractIndex
{
    protected string $index = 'test';
}
