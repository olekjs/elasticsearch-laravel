<?php

namespace Olekjs\Elasticsearch\Utils;

use Illuminate\Http\Client\Response;
use Olekjs\Elasticsearch\Contracts\ResponseInterface;
use Olekjs\Elasticsearch\Dto\FindResponseDto;

class FindResponse implements ResponseInterface
{
    public static function from(Response $response, array $data = []): FindResponseDto
    {
        return new FindResponseDto(
            index: data_get($response, '_index'),
            id: data_get($response, '_id'),
            version: data_get($response, '_version'),
            sequenceNumber: data_get($response, '_seq_no'),
            primaryTerm: data_get($response, '_primary_term'),
            found: data_get($response, 'found'),
            source: data_get($response, '_source'),
        );
    }
}
