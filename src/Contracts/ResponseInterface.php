<?php

namespace Olekjs\Elasticsearch\Contracts;

use Illuminate\Http\Client\Response;

interface ResponseInterface
{
    public static function from(Response $response): ResponseDtoInterface;
}
