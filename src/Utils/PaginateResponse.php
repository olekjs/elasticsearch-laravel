<?php

namespace Olekjs\Elasticsearch\Utils;

use Illuminate\Http\Client\Response;
use Olekjs\Elasticsearch\Contracts\ResponseInterface;
use Olekjs\Elasticsearch\Dto\PaginateResponseDto;

class PaginateResponse implements ResponseInterface
{
    public static function from(Response $response, array $data = []): PaginateResponseDto
    {
        return new PaginateResponseDto(
            perPage: data_get($data, 'per_page'),
            currentPage: data_get($data, 'current_page'),
            totalPages: data_get($data, 'total_pages'),
            totalDocuments: data_get($data, 'total_documents'),
            documents: SearchResponse::from($response)
        );
    }
}
