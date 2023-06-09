<?php

namespace Olekjs\Elasticsearch\Tests\Unit;

use LogicException;
use Olekjs\Elasticsearch\Builder\Builder;
use Olekjs\Elasticsearch\Client;
use Olekjs\Elasticsearch\Dto\FindResponseDto;
use Olekjs\Elasticsearch\Dto\PaginateResponseDto;
use Olekjs\Elasticsearch\Dto\SearchHitsDto;
use Olekjs\Elasticsearch\Dto\SearchResponseDto;
use Olekjs\Elasticsearch\Dto\ShardsResponseDto;
use Olekjs\Elasticsearch\Exceptions\NotFoundResponseException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class BuilderTest extends TestCase
{
    public function testIndexMethod(): void
    {
        $builder = Builder::query()->index('test');

        $this->assertSame('test', $builder->getIndex());
    }

    public function testWhereKeywordMethod(): void
    {
        $builder = Builder::query()->whereKeyword('email', 'test@test.com');

        $this->assertSame([
            'bool' => ['filter' => [['term' => ['email.keyword' => 'test@test.com']]]]
        ], $builder->getQuery());
    }

    public function testWhereMethod(): void
    {
        $builder = Builder::query()->where('name', 'test');

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
            fn(Builder $builder) => $builder->whereKeyword('email', 'test@test.com')
        );

        $this->assertSame([
            'bool' => ['filter' => [['term' => ['email.keyword' => 'test@test.com']]]]
        ], $builder->getQuery());

        $builder = Builder::query()->when(
            false,
            fn(Builder $builder) => $builder->whereKeyword('email', 'test@test.com')
        );

        $this->assertEmpty($builder->getBody());
    }

    public function testChainedMethods(): void
    {
        $builder = Builder::query()
            ->whereKeyword('email', 'test@test.com')
            ->whereKeyword('slug', 'test-slug')
            ->where('name', 'test')
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

    public function testOrWhereKeywordMethod(): void
    {
        $builder = Builder::query()
            ->index('test')
            ->whereKeyword('email', 'test@test.com')
            ->orWhereKeyword('username', 'tester');

        $this->assertSame(
            [
                'bool' => [
                    'filter' => [
                        ['term' => ['email.keyword' => 'test@test.com']]
                    ],
                    'should' => [
                        ['term' => ['username.keyword' => 'tester']],
                    ]
                ]
            ],
            $builder->getQuery()
        );

        $builder->orWhereKeyword('surname', 'tests');

        $this->assertSame(
            [
                'bool' => [
                    'filter' => [
                        ['term' => ['email.keyword' => 'test@test.com']]
                    ],
                    'should' => [
                        ['term' => ['username.keyword' => 'tester']],
                        ['term' => ['surname.keyword' => 'tests']],
                    ]
                ]
            ],
            $builder->getQuery()
        );
    }

    public function testOrWhereMethod(): void
    {
        $builder = Builder::query()
            ->index('test')
            ->where('email', 'test@test.com')
            ->orWhere('username', 'tester');

        $this->assertSame(
            [
                'bool' => [
                    'filter' => [
                        ['term' => ['email' => 'test@test.com']]
                    ],
                    'should' => [
                        ['term' => ['username' => 'tester']],
                    ]
                ]
            ],
            $builder->getQuery()
        );

        $builder->orWhere('surname', 'tests');

        $this->assertSame(
            [
                'bool' => [
                    'filter' => [
                        ['term' => ['email' => 'test@test.com']]
                    ],
                    'should' => [
                        ['term' => ['username' => 'tester']],
                        ['term' => ['surname' => 'tests']],
                    ]
                ]
            ],
            $builder->getQuery()
        );
    }

    public function testOrWhereInMethod(): void
    {
        $builder = Builder::query()
            ->index('test')
            ->whereIn('email', ['test@test.com'])
            ->orWhereIn('username', ['tester']);

        $this->assertSame(
            [
                'bool' => [
                    'filter' => [
                        ['terms' => ['email' => ['test@test.com']]]
                    ],
                    'should' => [
                        ['terms' => ['username' => ['tester']]],
                    ]
                ]
            ],
            $builder->getQuery()
        );

        $builder->orWhereIn('surname', ['tests']);

        $this->assertSame(
            [
                'bool' => [
                    'filter' => [
                        ['terms' => ['email' => ['test@test.com']]]
                    ],
                    'should' => [
                        ['terms' => ['username' => ['tester']]],
                        ['terms' => ['surname' => ['tests']]],
                    ]
                ]
            ],
            $builder->getQuery()
        );
    }

    public function testFindMethod(): void
    {
        $client = $this->createMock(Client::class);

        $client
            ->method('find')
            ->will(
                $this->returnValue(
                    new FindResponseDto(
                        'test',
                        '1',
                        1,
                        1,
                        1,
                        true,
                        ['_id' => 1]
                    )
                )
            );

        $result = Builder::query($client)->index('test')->find(1);

        $this->assertInstanceOf(FindResponseDto::class, $result);

        $this->assertSame('1', $result->getId());
        $this->assertSame('test', $result->getIndex());
    }

    public function testFindOrFailMethod(): void
    {
        $this->expectException(NotFoundResponseException::class);

        $client = $this->createMock(Client::class);

        $client
            ->method('findOrFail')
            ->will($this->throwException(
                new NotFoundResponseException('response', Response::HTTP_NOT_FOUND)
            ));

        Builder::query($client)->index('test')->findOrFail(1);
    }

    public function testWhereRangeMethod(): void
    {
        $builder = Builder::query()->whereRange('test', 10, '>');

        $this->assertSame(
            ['bool' => ['filter' => [['range' => ['test' => ['>' => 10]]]]]],
            $builder->getQuery()
        );
    }

    public function testWhereBetween(): void
    {
        $builder = Builder::query()->whereBetween('test', [10, 20]);

        $this->assertSame(
            ['bool' => ['filter' => [['range' => ['test' => ['gte' => 10]]], ['range' => ['test' => ['lte' => 20]]]]]],
            $builder->getQuery()
        );
    }

    public function testWhereBetweenInvalidValuesArrayException(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Provide two values');

        Builder::query()->whereBetween('test', [10]);
    }

    public function testCountMethod(): void
    {
        $client = $this->createMock(Client::class);

        $client
            ->method('count')
            ->will($this->returnValue(10));

        $result = Builder::query($client)->index('test')->count();

        $this->assertSame(10, $result);
    }

    public function testPaginateMethod(): void
    {
        $client = $this->createMock(Client::class);

        $client
            ->method('paginate')
            ->will(
                $this->returnValue(
                    new PaginateResponseDto(
                        100,
                        10,
                        11,
                        1000,
                        new SearchResponseDto(
                            1,
                            false,
                            new ShardsResponseDto(1, 1, 1),
                            new SearchHitsDto([], 1.1, [])
                        )
                    )
                )
            );

        $result = Builder::query($client)->index('test')->paginate(10, 100);

        $this->assertInstanceOf(PaginateResponseDto::class, $result);
        $this->assertSame(10, $result->getCurrentPage());
        $this->assertSame(100, $result->getPerPage());
    }

    public function testWhereLikeMethod(): void
    {
        $builder = Builder::query()->whereLike('name', '*test*');

        $this->assertSame([
            'bool' => ['filter' => [['wildcard' => ['name' => '*test*']]]]
        ], $builder->getQuery());
    }

    public function testOrWhereLikeMethod(): void
    {
        $builder = Builder::query()->orWhereLike('name', '*test*');

        $this->assertSame([
            'bool' => ['should' => [['wildcard' => ['name' => '*test*']]]]
        ], $builder->getQuery());
    }

    public function testWhereNotMethod(): void
    {
        $builder = Builder::query()->whereNot('name', 'test');

        $this->assertSame([
            'bool' => ['must_not' => [['term' => ['name' => 'test']]]]
        ], $builder->getQuery());
    }

    public function testOrWhereNotMethod(): void
    {
        $builder = Builder::query()->orWhereNot('name', 'test');

        $this->assertSame([
            'bool' => ['should' => [['bool' => ['must_not' => [['term' => ['name' => 'test']]]]]]]
        ], $builder->getQuery());
    }
}
