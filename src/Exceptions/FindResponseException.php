<?php

namespace Elasticsearch\Exceptions;

class FindResponseException extends CoreException
{
    public function __construct(string $response, int $status)
    {
        parent::__construct($response, $status);
    }
}
