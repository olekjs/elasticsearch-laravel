<?php

namespace Unit;

use Olekjs\Elasticsearch\Builder\Builder;
use Olekjs\Elasticsearch\Client;
use Olekjs\Elasticsearch\Contracts\AbstractIndex;
use Olekjs\Elasticsearch\Dto\SearchResponseDto;
use Olekjs\Elasticsearch\Tests\Support\CustomIndex;
use PHPUnit\Framework\TestCase;

class CustomIndexTest extends TestCase
{
    public function testCustomIndexMethod(): void
    {
        $customIndex = new CustomIndex();

        $this->assertSame('test', $customIndex->getIndexName());
        $this->assertInstanceOf(Builder::class, $customIndex->query());
        $this->assertInstanceOf(Builder::class, CustomIndex::query());
        $this->assertSame('test', CustomIndex::query()->getIndex());
    }
}
