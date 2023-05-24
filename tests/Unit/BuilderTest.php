<?php

namespace Olekjs\Elasticsearch\Tests\Unit;

use Olekjs\Elasticsearch\Builder\Builder;
use Olekjs\Elasticsearch\Client;
use Olekjs\Elasticsearch\Dto\SearchResponseDto;
use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase
{
    public function testIndexMethod(): void
    {
        $builder = Builder::query()->index('test');

        $this->assertSame('test', $builder->getIndex());
    }

    public function testWhereMethod(): void
    {
        $builder = Builder::query()->where('email', 'test@test.com');

        $this->assertSame([
            'bool' => ['filter' => [['term' => ['email.keyword' => 'test@test.com']]]]
        ], $builder->getQuery());
    }

    public function testWhereLikeMethod(): void
    {
        $builder = Builder::query()->whereLike('name', 'test');

        $this->assertSame([
            'bool' => ['filter' => [['term' => ['name' => 'test']]]]
        ], $builder->getQuery());
    }

    public function testWhereInMethod(): void
    {
        $builder = Builder::query()->whereIn('_id', [123, 321]);

        $this->assertSame([
            'bool' => ['filter' => [['terms' => ['_id' => [123, 321]]]]]
        ], $builder->getQuery());
    }

    public function testOffsetMethod(): void
    {
        $builder = Builder::query()->offset(10);

        $this->assertSame(['from' => 10], $builder->getBody());
    }

    public function testLimitMethod(): void
    {
        $builder = Builder::query()->limit(10);

        $this->assertSame(['size' => 10], $builder->getBody());
    }

    public function testGetMethod(): void
    {
        $client = $this->getMockBuilder(Client::class)->getMock();

        $results = Builder::query($client)->index('test')->get();

        $this->assertInstanceOf(SearchResponseDto::class, $results);
    }

    public function testConditionableTrait(): void
    {
        $builder = Builder::query()->when(
            true,
            fn(Builder $builder) => $builder->where('email', 'test@test.com')
        );

        $this->assertSame([
            'bool' => ['filter' => [['term' => ['email.keyword' => 'test@test.com']]]]
        ], $builder->getQuery());

        $builder = Builder::query()->when(
            false,
            fn(Builder $builder) => $builder->where('email', 'test@test.com')
        );

        $this->assertEmpty($builder->getBody());
    }

    public function testChainedMethods(): void
    {
        $builder = Builder::query()
            ->where('email', 'test@test.com')
            ->where('slug', 'test-slug')
            ->whereLike('name', 'test')
            ->whereIn('_id', [123, 321])
            ->limit(10);

        $this->assertSame(['size' => 10], $builder->getBody());

        $this->assertSame(
            [
                'bool' => [
                    'filter' => [
                        [
                            'term' => [
                                'email.keyword' => 'test@test.com'
                            ]
                        ],
                        [
                            'term' => [
                                'slug.keyword' => 'test-slug'
                            ]
                        ],
                        [
                            'term' => [
                                'name' => 'test'
                            ]
                        ],
                        [
                            'terms' => [
                                '_id' => [123, 321]
                            ]
                        ]
                    ]
                ]
            ],
            $builder->getQuery()
        );
    }
}
