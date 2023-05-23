<?php

namespace Elasticsearch\Exceptions;

class DeleteResponseException extends CoreException
{
    public function __construct(string $response, int $status)
    {
        parent::__construct($response, $status);
    }
}
