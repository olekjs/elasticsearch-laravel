<?php

namespace Olekjs\Elasticsearch\Tests\Unit;

use Olekjs\Elasticsearch\Dto\FindResponseDto;
use Olekjs\Elasticsearch\Dto\IndexResponseDto;
use Olekjs\Elasticsearch\Dto\PaginateResponseDto;
use Olekjs\Elasticsearch\Dto\SearchHitDto;
use Olekjs\Elasticsearch\Dto\SearchHitsDto;
use Olekjs\Elasticsearch\Dto\SearchResponseDto;
use Olekjs\Elasticsearch\Dto\ShardsResponseDto;
use PHPUnit\Framework\TestCase;
use Illuminate\Contracts\Support\Arrayable;
use Olekjs\Elasticsearch\Contracts\Collectionable;

class DtosTest extends TestCase
{
    public function testDtosAreArrayable(): void
    {
        $dtos = [
            FindResponseDto::class,
            IndexResponseDto::class,
            PaginateResponseDto::class,
            SearchHitDto::class,
            SearchHitsDto::class,
            SearchResponseDto::class,
            ShardsResponseDto::class
        ];

        foreach ($dtos as $dto) {
            $interfaces = class_implements($dto);

            $this->assertContains(Arrayable::class, $interfaces);
        }
    }

    public function testSearchResponseIsCollectionable(): void
    {
        $interfaces = class_implements(SearchResponseDto::class);

        $this->assertContains(Collectionable::class, $interfaces);
    }
}
