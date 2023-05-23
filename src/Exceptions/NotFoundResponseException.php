<?php

namespace Olekjs\Elasticsearch\Exceptions;

class NotFoundResponseException extends CoreException
{
    public function __construct(string $response, int $status)
    {
        parent::__construct($response, $status);
    }
}
