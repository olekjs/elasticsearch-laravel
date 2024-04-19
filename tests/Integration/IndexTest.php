<?php

namespace Olekjs\Elasticsearch\Tests\Unit;

use Illuminate\Support\Facades\Http;
use Olekjs\Elasticsearch\Index\Index;
use Olekjs\Elasticsearch\Tests\TestCase;

class IndexTest extends TestCase
{
    public function testCreateMethod(): void
    {
        Http::fake(function () {
            return Http::response(
                file_get_contents('tests/Responses/store_index_response.json')
            );
        });

        $index = new Index();
        $added = $index->create('index');

        $this->assertTrue($added);
    }

    public function testDeleteMethod(): void
    {
        Http::fake(function () {
            return Http::response(
                file_get_contents('tests/Responses/delete_index_response.json')
            );
        });

        $index = new Index();
        $deleted = $index->delete('index');

        $this->assertTrue($deleted);
    }
}