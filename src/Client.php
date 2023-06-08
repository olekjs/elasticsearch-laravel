<?php

namespace Olekjs\Elasticsearch;

use Olekjs\Elasticsearch\Contracts\AbstractClient;
use Olekjs\Elasticsearch\Contracts\ClientInterface;
use Olekjs\Elasticsearch\Dto\IndexResponseDto;
use Olekjs\Elasticsearch\Dto\PaginateResponseDto;
use Olekjs\Elasticsearch\Dto\SearchResponseDto;
use Olekjs\Elasticsearch\Exceptions\ConflictResponseException;
use Olekjs\Elasticsearch\Exceptions\CoreException;
use Olekjs\Elasticsearch\Exceptions\DeleteResponseException;
use Olekjs\Elasticsearch\Exceptions\FindResponseException;
use Olekjs\Elasticsearch\Exceptions\IndexNotFoundResponseException;
use Olekjs\Elasticsearch\Exceptions\IndexResponseException;
use Olekjs\Elasticsearch\Exceptions\NotFoundResponseException;
use Olekjs\Elasticsearch\Exceptions\SearchResponseException;
use Olekjs\Elasticsearch\Exceptions\UpdateResponseException;
use Olekjs\Elasticsearch\Utils\FindResponse;
use Olekjs\Elasticsearch\Utils\IndexResponse;
use Olekjs\Elasticsearch\Utils\PaginateResponse;
use Olekjs\Elasticsearch\Utils\SearchResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Olekjs\Elasticsearch\Dto\FindResponseDto;

class Client extends AbstractClient implements ClientInterface
{
    /**
     * @throws SearchResponseException
     */
    public function search(string $index, array $data): SearchResponseDto
    {
        $response = $this->getBaseClient()
            ->post("$index/_search", $data);

        if ($response->clientError()) {
            $this->throwSearchResponseException(
                data_get($response, 'error.reason'),
                $response->status(),
            );
        }

        return SearchResponse::from($response);
    }

    /**
     * @throws IndexNotFoundResponseException
     * @throws FindResponseException
     */
    public function find(string $index, string|int $id): ?FindResponseDto
    {
        $response = $this->getBaseClient()
            ->get("$index/_doc/$id");

        if ($response->notFound() && data_get($response, 'error.type') === 'index_not_found_exception') {
            $this->throwIndexNotFoundException(
                data_get($response, 'error.reason')
            );
        }

        if ($response->notFound() && !data_get($response, 'found')) {
            return null;
        }

        if ($response->clientError()) {
            throw new FindResponseException(
                data_get($response, 'error.reason'),
                $response->status(),
            );
        }

        return FindResponse::from($response);
    }

    /**
     * @throws NotFoundResponseException
     * @throws IndexNotFoundResponseException
     * @throws FindResponseException
     */
    public function findOrFail(string $index, string|int $id): FindResponseDto
    {
        $result = $this->find($index, $id);

        if (is_null($result)) {
            $this->throwNotFoundException(
                "Document [$id] in index [$index] not found."
            );
        }

        return $result;
    }

    /**
     * @throws IndexResponseException
     */
    public function create(string $index, string|int $id, array $data): IndexResponseDto
    {
        $response = $this->getBaseClient()
            ->post("$index/_create/$id", $data);

        if ($response->clientError()) {
            $this->throwIndexResponseException(
                json_encode($response->json()),
                $response->status()
            );
        }

        return IndexResponse::from($response);
    }

    /**
     * @throws NotFoundResponseException
     * @throws UpdateResponseException
     * @throws ConflictResponseException
     */
    public function update(
        string $index,
        string|int $id,
        array $data = [],
        array $script = [],
        ?int $primaryTerm = null,
        ?int $sequenceNumber = null
    ): IndexResponseDto {
        $baseUrl = "$index/_update/$id";

        if (!is_null($primaryTerm) && !is_null($sequenceNumber)) {
            $strictUrl = http_build_query([
                'if_primary_term' => $primaryTerm,
                'if_seq_no' => $sequenceNumber,
            ]);

            $baseUrl = $baseUrl . '?' . $strictUrl;
        }

        $body = match (true) {
            empty($data) => ['script' => $script],
            empty($script) => ['doc' => $data]
        };

        $response = $this->getBaseClient()
            ->post($baseUrl, $body);

        if ($response->notFound() && data_get($response, 'status') === SymfonyResponse::HTTP_NOT_FOUND) {
            $this->throwNotFoundException(
                json_encode($response->json())
            );
        }

        if (
            !is_null($primaryTerm)
            && !is_null($sequenceNumber)
            && $response->clientError()
            && data_get($response, 'status') === SymfonyResponse::HTTP_CONFLICT
        ) {
            $this->throwConflictResponseException(
                json_encode($response->json())
            );
        }

        if ($response->clientError()) {
            $this->throwUpdateResponseException(
                json_encode($response->json()),
                $response->status()
            );
        }

        return IndexResponse::from($response);
    }

    /**
     * @throws DeleteResponseException
     * @throws NotFoundResponseException
     */
    public function delete(string $index, string|int $id): IndexResponseDto
    {
        $response = $this->getBaseClient()
            ->delete("$index/_doc/$id");

        if ($response->notFound() && data_get($response, 'result') === 'not_found') {
            $this->throwNotFoundException(
                json_encode($response->json())
            );
        }

        if ($response->clientError()) {
            $this->throwDeleteResponseException(
                json_encode($response->json()),
                $response->status()
            );
        }

        return IndexResponse::from($response);
    }

    /**
     * @throws SearchResponseException
     */
    public function searchWhereIn(string $index, string $field, array $values): SearchResponseDto
    {
        return $this->search($index, [
            'query' => [
                $field => [
                    'values' => $values
                ]
            ]
        ]);
    }

    /**
     * @throws SearchResponseException
     */
    public function searchWhereKeyword(string $index, string $field, string $value): SearchResponseDto
    {
        return $this->search($index, [
            'query' => [
                'term' => [
                    $field . '.keyword' => $value
                ]
            ]
        ]);
    }

    /**
     * @throws SearchResponseException
     */
    public function searchWhereLike(string $index, string $field, string|int|float $value): SearchResponseDto
    {
        return $this->search($index, [
            'query' => [
                'wildcard' => [
                    $field => [
                        'value' => $value
                    ]
                ]
            ]
        ]);
    }

    /**
     * @throws NotFoundResponseException
     * @throws UpdateResponseException
     * @throws ConflictResponseException
     */
    public function increment(string $index, string|int $id, string $field, int $value = 1): IndexResponseDto
    {
        return $this->update($index, $id, [], [
            'source' => "ctx._source.$field += params.count",
            'params' => [
                'count' => $value
            ]
        ]);
    }

    /**
     * @throws NotFoundResponseException
     * @throws UpdateResponseException
     * @throws ConflictResponseException
     */
    public function decrement(string $index, string|int $id, string $field, int $value = 1): IndexResponseDto
    {
        return $this->update($index, $id, [], [
            'source' => "ctx._source.$field -= params.count",
            'params' => [
                'count' => $value
            ]
        ]);
    }

    /**
     * @throws SearchResponseException
     * @throws CoreException
     */
    public function count(string $index, array $data = []): int
    {
        if (empty($data)) {
            $data = [
                'query' => [
                    'match_all' => (object)[]
                ]
            ];
        }

        $response = $this->getBaseClient()
            ->post("$index/_count", $data);

        if ($response->clientError()) {
            $this->throwSearchResponseException(
                data_get($response, 'error.reason'),
                $response->status(),
            );
        }

        return data_get(
            $response,
            'count',
            fn() => throw new CoreException('Wrong response type')
        );
    }

    /**
     * @throws SearchResponseException
     * @throws CoreException
     */
    public function paginate(string $index, array $data = [], int $page = 1, int $perPage = 25): PaginateResponseDto
    {
        if (empty($data)) {
            $data = [
                'query' => [
                    'match_all' => (object)[]
                ]
            ];
        }

        $totalDocuments = $this->count($index, $data);

        $pages = (int)ceil(
            $totalDocuments === 0 ? 0 : ($totalDocuments <= $perPage ? 1 : $totalDocuments / $perPage)
        );

        $data['from'] = $page * $perPage;
        $data['size'] = $perPage;

        $response = $this->getBaseClient()
            ->post("$index/_search", $data);

        if ($response->clientError()) {
            $this->throwSearchResponseException(
                data_get($response, 'error.reason'),
                $response->status(),
            );
        }

        return PaginateResponse::from($response, [
            'per_page' => $perPage,
            'current_page' => $page,
            'total_pages' => $pages,
            'total_documents' => $totalDocuments
        ]);
    }
}
