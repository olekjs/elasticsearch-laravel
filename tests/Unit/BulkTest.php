<?php

namespace Olekjs\Elasticsearch\Tests\Unit;

use Generator;
use Olekjs\Elasticsearch\Bulk\Bulk;
use PHPUnit\Framework\TestCase;

class BulkTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testBulk(callable $callback, array $expected): void
    {
        $actual = $callback();

        $this->assertSame($expected, $actual);
    }

    public static function dataProvider(): Generator
    {
        yield [
            function () {
                $bulk = new Bulk();

                $bulk->add(action: 'index', index: 'products', id: 1, data: ['name' => 'test']);

                return $bulk->getDocuments();
            },
            [
                [
                    'index' => [
                        '_index' => 'products',
                        '_id' => '1'
                    ]
                ],
                [
                    'name' => 'test'
                ]
            ]
        ];
        yield [
            function () {
                $bulk = new Bulk();

                $bulk->add(action: 'update', index: 'products', id: 2, data: ['name' => 'test']);

                return $bulk->getDocuments();
            },
            [
                [
                    'update' => [
                        '_index' => 'products',
                        '_id' => '2'
                    ]
                ],
                [
                    'doc' => [
                        'name' => 'test'
                    ]
                ]
            ]
        ];
        yield [
            function () {
                $bulk = new Bulk();

                $bulk->add(action: 'delete', index: 'products', id: 4);

                return $bulk->getDocuments();
            },
            [
                [
                    'delete' => [
                        '_index' => 'products',
                        '_id' => '4'
                    ]
                ]
            ]
        ];
        yield [
            function () {
                $bulk = new Bulk();

                $bulk->addMany([
                    ['action' => 'index', 'index' => 'products', 'id' => 4, 'data' => ['name' => 'test']],
                    ['action' => 'delete', 'index' => 'products', 'id' => 5, 'data' => []],
                    ['action' => 'update', 'index' => 'products', 'id' => 6, 'data' => ['name' => 'test']],
                ]);

                return $bulk->getDocuments();
            },
            [
                [
                    'index' => [
                        '_index' => 'products',
                        '_id' => '4'
                    ]
                ],
                [
                    'name' => 'test'
                ],
                [
                    'delete' => [
                        '_index' => 'products',
                        '_id' => '5'
                    ],
                ],
                [
                    'update' => [
                        '_index' => 'products',
                        '_id' => '6'
                    ]
                ],
                [
                    'doc' => [
                        'name' => 'test'
                    ]
                ]
            ]
        ];
    }
}
