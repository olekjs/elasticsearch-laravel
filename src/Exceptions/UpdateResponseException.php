<?php

namespace Olekjs\Elasticsearch\Exceptions;

class UpdateResponseException extends CoreException
{
    public function __construct(string $response, int $status)
    {
        parent::__construct($response, $status);
    }
}
