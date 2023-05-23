<?php

namespace Olekjs\Elasticsearch\Utils;

use Illuminate\Http\Client\Response;
use Olekjs\Elasticsearch\Contracts\ResponseInterface;
use Olekjs\Elasticsearch\Dto\SearchHitDto;
use Olekjs\Elasticsearch\Dto\SearchHitsDto;
use Olekjs\Elasticsearch\Dto\SearchResponseDto;
use Olekjs\Elasticsearch\Dto\ShardsResponseDto;

class SearchResponse implements ResponseInterface
{
    public static function from(Response $response): SearchResponseDto
    {
        return new SearchResponseDto(
            took: data_get($response, 'took'),
            isTimedOut: data_get($response, 'timed_out'),
            shards: new ShardsResponseDto(
                total: data_get($response, '_shards.total'),
                successful: data_get($response, '_shards.successful'),
                failed: data_get($response, '_shards.failed'),
                skipped: data_get($response, '_shards.skipped'),
            ),
            results: new SearchHitsDto(
                total: data_get($response, 'hits.total'),
                maxScore: data_get($response, 'hits.max_score'),
                hits: array_map(
                    fn(array $hit): SearchHitDto => new SearchHitDto(
                        index: data_get($hit, '_index'),
                        id: data_get($hit, '_id'),
                        score: data_get($hit, '_score'),
                        source: data_get($hit, '_source'),
                    ),
                    data_get($response, 'hits.hits'),
                )
            )
        );
    }
}
