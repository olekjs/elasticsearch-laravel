<?php

namespace Olekjs\Elasticsearch\Contracts;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use Olekjs\Elasticsearch\Exceptions\ConflictResponseException;
use Olekjs\Elasticsearch\Exceptions\DeleteResponseException;
use Olekjs\Elasticsearch\Exceptions\IndexNotFoundResponseException;
use Olekjs\Elasticsearch\Exceptions\IndexResponseException;
use Olekjs\Elasticsearch\Exceptions\NotFoundResponseException;
use Olekjs\Elasticsearch\Exceptions\SearchResponseException;
use Olekjs\Elasticsearch\Exceptions\UpdateResponseException;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractClient
{
    public function getBaseClient(): PendingRequest
    {
        $apiKey = config('services.elasticsearch.api_key');
        $port = config('services.elasticsearch.port');

        $url = Str::of(config('services.elasticsearch.url'))
            ->when(!is_null($port), fn(Stringable $str) => $str->append(':' . $port));

        $http = Http::acceptJson()
            ->asJson()
            ->baseUrl($url);

        if (!is_null($apiKey)) {
            $http->withToken($apiKey, 'ApiKey');
        }

        return $http;
    }

    /**
     * @throws NotFoundResponseException
     */
    public function throwNotFoundException(string $message, int $code = Response::HTTP_NOT_FOUND): void
    {
        throw new NotFoundResponseException(
            $message,
            $code
        );
    }

    /**
     * @throws IndexNotFoundResponseException
     */
    public function throwIndexNotFoundException(string $message, int $code = Response::HTTP_NOT_FOUND): void
    {
        throw new IndexNotFoundResponseException(
            $message,
            $code
        );
    }

    /**
     * @throws SearchResponseException
     */
    public function throwSearchResponseException(string $message, int $code = Response::HTTP_BAD_REQUEST): void
    {
        throw new SearchResponseException(
            $message,
            $code
        );
    }

    /**
     * @throws IndexResponseException
     */
    public function throwIndexResponseException(string $message, int $code = Response::HTTP_BAD_REQUEST): void
    {
        throw new IndexResponseException(
            $message,
            $code
        );
    }

    /**
     * @throws DeleteResponseException
     */
    public function throwDeleteResponseException(string $message, int $code = Response::HTTP_BAD_REQUEST): void
    {
        throw new DeleteResponseException(
            $message,
            $code
        );
    }

    /**
     * @throws UpdateResponseException
     */
    public function throwUpdateResponseException(string $message, int $code = Response::HTTP_BAD_REQUEST): void
    {
        throw new UpdateResponseException(
            $message,
            $code
        );
    }

    /**
     * @throws ConflictResponseException
     */
    public function throwConflictResponseException(string $message, int $code = Response::HTTP_CONFLICT): void
    {
        throw new ConflictResponseException(
            $message,
            $code
        );
    }
}
