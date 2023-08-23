<?php

namespace Olekjs\Elasticsearch\Tests\Unit;

use Generator;
use Olekjs\Elasticsearch\Aggregation\Aggregation;
use PHPUnit\Framework\TestCase;

class AggregationTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testAggregation(callable $callback, array $expected): void
    {
        $actual = $callback();

        $this->assertSame($expected, $actual);
    }

    public static function dataProvider(): Generator
    {
        yield [
            function () {
                $aggregation = new Aggregation('category-aggregation', [
                    'terms' => ['field' => 'category']
                ]);

                return $aggregation->toRequestArray();
            },
            [
                'category-aggregation' => [
                    'terms' => [
                        'field' => 'category',
                    ]
                ]
            ]
        ];
        yield [
            function () {
                $aggregation = new Aggregation('price-max-aggregation', [
                    'max' => ['field' => 'price']
                ]);


                return $aggregation->toRequestArray();
            },
            [
                'price-max-aggregation' => [
                    'max' => [
                        'field' => 'price',
                    ]
                ]
            ]
        ];
        yield [
            function () {
                $nestedSubAggregation = new Aggregation('nested-aggregation', [
                    'terms' => ['field' => 'nested']
                ]);

                $minPriceSubAggregation = new Aggregation('price-min-aggregation', [
                    'min' => ['field' => 'price']
                ]);

                $maxPriceSubAggregation = new Aggregation('price-max-aggregation', [
                    'max' => ['field' => 'price']
                ]);

                $maxPriceSubAggregation->addSubAggregation($nestedSubAggregation);

                $aggregation = new Aggregation('category-aggregation', [
                    'terms' => ['field' => 'category']
                ], [$minPriceSubAggregation, $maxPriceSubAggregation]);


                return $aggregation->toRequestArray();
            },
            [
                'category-aggregation' => [
                    'terms' => [
                        'field' => 'category',
                    ],
                    'aggs' => [
                        'price-min-aggregation' => [
                            'min' => [
                                'field' => 'price',
                            ]
                        ],
                        'price-max-aggregation' => [
                            'max' => [
                                'field' => 'price',
                            ],
                            'aggs' => [
                                'nested-aggregation' => [
                                    'terms' => [
                                        'field' => 'nested'
                                    ]
                                ]
                            ]
                        ],
                    ]
                ]
            ]
        ];
    }
}
