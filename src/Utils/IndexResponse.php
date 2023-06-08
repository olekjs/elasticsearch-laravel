<?php

namespace Olekjs\Elasticsearch\Utils;

use Illuminate\Http\Client\Response;
use Olekjs\Elasticsearch\Contracts\ResponseInterface;
use Olekjs\Elasticsearch\Dto\IndexResponseDto;
use Olekjs\Elasticsearch\Dto\ShardsResponseDto;

class IndexResponse implements ResponseInterface
{
    public static function from(Response $response, array $data = []): IndexResponseDto
    {
        return new IndexResponseDto(
            index: data_get($response, '_index'),
            id: data_get($response, '_id'),
            version: data_get($response, '_version'),
            result: data_get($response, 'result'),
            shards: new ShardsResponseDto(
                total: data_get($response, '_shards.total'),
                successful: data_get($response, '_shards.successful'),
                failed: data_get($response, '_shards.failed'),
            ),
            sequenceNumber: data_get($response, '_seq_no'),
            primaryTerm: data_get($response, '_primary_term')
        );
    }
}
