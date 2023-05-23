<?php

namespace Olekjs\Elasticsearch\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            'Olekjs\Elasticsearch\Providers\ElasticsearchServiceProvider',
        ];
    }
}
