<?php

namespace Olekjs\Elasticsearch\Tests\Unit;

use Illuminate\Support\Facades\Http;
use Olekjs\Elasticsearch\Alias\Alias;
use Olekjs\Elasticsearch\Tests\TestCase;

class AliasTest extends TestCase
{
    public function testGetIndicesForAliasMethod(): void
    {
        Http::fake(function () {
            return Http::response(
                file_get_contents('tests/Responses/list_alias_indices_response.json')
            );
        });

        $alias = new Alias();
        $indices = $alias->getIndicesForAlias('alias');

        $this->assertSame(['test_1', 'test_2'], $indices);
    }

    public function testAddMethod(): void
    {
        Http::fake(function () {
            return Http::response(
                file_get_contents('tests/Responses/alias_run_actions_success_response.json')
            );
        });

        $alias = new Alias();
        $added = $alias->add('index', 'alias');

        $this->assertTrue($added);
    }

    public function testRemoveMethod(): void
    {
        Http::fake(function () {
            return Http::response(
                file_get_contents('tests/Responses/alias_run_actions_success_response.json')
            );
        });

        $alias = new Alias();
        $added = $alias->remove('index', 'alias');

        $this->assertTrue($added);
    }

    public function testRunActionsMethod(): void
    {
        Http::fake(function () {
            return Http::response(
                file_get_contents('tests/Responses/alias_run_actions_success_response.json')
            );
        });

        $alias = new Alias();
        $response = $alias->runActions([
            [
                'remove' => [
                    'index' => 'test',
                    'alias' => 'test_alias',
                ]
            ]
        ]);

        $this->assertTrue($response->successful());
    }
}