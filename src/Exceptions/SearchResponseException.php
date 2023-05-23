<?php

namespace Elasticsearch\Exceptions;

class SearchResponseException extends CoreException
{
    public function __construct(string $response, int $status)
    {
        parent::__construct($response, $status);
    }
}
