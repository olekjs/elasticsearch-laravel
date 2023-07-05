<?php

namespace Olekjs\Elasticsearch\Utils;

use Illuminate\Http\Client\Response;
use Olekjs\Elasticsearch\Contracts\ResponseInterface;
use Olekjs\Elasticsearch\Dto\BulkItemResponseDto;
use Olekjs\Elasticsearch\Dto\BulkResponseDto;
use Olekjs\Elasticsearch\Dto\IndexResponseDto;
use Olekjs\Elasticsearch\Dto\ShardsResponseDto;

class BulkResponse implements ResponseInterface
{
    public static function from(Response $response, array $data = []): BulkResponseDto
    {
        $items = array_map(
            fn(array $items): array => array_map(
                fn(array $data, string $action): BulkItemResponseDto => new BulkItemResponseDto(
                    action: $action,
                    data: new IndexResponseDto(
                        index: data_get($data, '_index'),
                        id: data_get($data, '_id'),
                        version: data_get($data, '_version'),
                        result: data_get($data, 'result'),
                        shards: new ShardsResponseDto(
                            total: data_get($data, '_shards.total'),
                            successful: data_get($data, '_shards.successful'),
                            failed: data_get($data, '_shards.failed'),
                        ),
                        sequenceNumber: data_get($data, '_seq_no'),
                        primaryTerm: data_get($data, '_primary_term')
                    )
                ),
                $items,
                array_keys($items),
            ), data_get($response, 'items')
        );

        return new BulkResponseDto(
            took: data_get($response, 'took'),
            errors: data_get($response, 'errors'),
            items: collect($items)->flatten()->all(),
        );
    }
}
