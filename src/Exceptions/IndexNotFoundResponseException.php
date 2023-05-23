<?php

namespace Elasticsearch\Exceptions;

class IndexNotFoundResponseException extends CoreException
{
    public function __construct(string $response, int $status)
    {
        parent::__construct($response, $status);
    }
}
