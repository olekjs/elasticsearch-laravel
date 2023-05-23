<?php

namespace Elasticsearch;

use Elasticsearch\Contracts\ClientInterface;
use Elasticsearch\Dto\IndexResponseDto;
use Elasticsearch\Dto\SearchHitDto;
use Elasticsearch\Dto\SearchHitsDto;
use Elasticsearch\Dto\SearchResponseDto;
use Elasticsearch\Dto\ShardsResponseDto;
use Elasticsearch\Exceptions\ConflictResponseException;
use Elasticsearch\Exceptions\DeleteResponseException;
use Elasticsearch\Exceptions\FindResponseException;
use Elasticsearch\Exceptions\IndexNotFoundResponseException;
use Elasticsearch\Exceptions\IndexResponseException;
use Elasticsearch\Exceptions\NotFoundResponseException;
use Elasticsearch\Exceptions\SearchResponseException;
use Elasticsearch\Exceptions\UpdateResponseException;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Elasticsearch\Dto\FindResponseDto;

class Client implements ClientInterface
{
    /**
     * @throws SearchResponseException
     */
    public function search(string $index, array $data): SearchResponseDto
    {
        $response = Http::acceptJson()
            ->asJson()
            ->baseUrl(config('services.elasticsearch.url'))
            ->post("$index/_search", $data);

        if ($response->clientError()) {
            throw new SearchResponseException(
                data_get($response, 'error.reason'),
                $response->status(),
            );
        }

        return new SearchResponseDto(
            took: data_get($response, 'took'),
            isTimedOut: data_get($response, 'timed_out'),
            shards: new ShardsResponseDto(
                total: data_get($response, '_shards.total'),
                successful: data_get($response, '_shards.successful'),
                failed: data_get($response, '_shards.failed'),
                skipped: data_get($response, '_shards.skipped'),
            ),
            hits: new SearchHitsDto(
                total: data_get($response, 'hits.total'),
                maxScore: data_get($response, 'hits.max_score'),
                hits: array_map(
                    fn(array $hit): SearchHitDto => new SearchHitDto(
                        index: data_get($hit, '_index'),
                        id: data_get($hit, '_id'),
                        score: data_get($hit, '_score'),
                        source: data_get($hit, '_source'),
                    ),
                    data_get($response, 'hits.hits'),
                )
            )
        );
    }

    /**
     * @throws IndexNotFoundResponseException
     * @throws FindResponseException
     */
    public function find(string $index, string|int $id): ?FindResponseDto
    {
        $response = Http::acceptJson()
            ->asJson()
            ->baseUrl(config('services.elasticsearch.url'))
            ->get("$index/_doc/$id");

        if ($response->notFound() && data_get($response, 'error.type') === 'index_not_found_exception') {
            throw new IndexNotFoundResponseException(
                data_get($response, 'error.reason'),
                $response->status(),
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

        return new FindResponseDto(
            index: data_get($response, '_index'),
            id: data_get($response, '_id'),
            version: data_get($response, '_version'),
            sequenceNumber: data_get($response, '_seq_no'),
            primaryTerm: data_get($response, '_primary_term'),
            found: data_get($response, 'found'),
            source: data_get($response, '_source'),
        );
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
            throw new NotFoundResponseException(
                "Document [$id] in index [$index] not found.",
                SymfonyResponse::HTTP_NOT_FOUND
            );
        }

        return $result;
    }

    /**
     * @throws IndexResponseException
     */
    public function create(string $index, string|int $id, array $data): IndexResponseDto
    {
        $response = Http::acceptJson()
            ->asJson()
            ->baseUrl(config('services.elasticsearch.url'))
            ->post("$index/_create/$id", $data);

        if ($response->clientError()) {
            throw new IndexResponseException(
                json_encode($response->json()),
                $response->status()
            );
        }

        return new IndexResponseDto(
            index: data_get($response, '_index'),
            id: data_get($response, '_id'),
            version: data_get($response, '_version'),
            result: data_get($response, 'result'),
            shards: new ShardsResponseDto(
                total: data_get($response, '_shards.total'),
                successful: data_get($response, '_shards.successful'),
                failed: data_get($response, '_shards.failed'),
            ),
            sequenceNumber: data_get($response, '_seq_no'),
            primaryTerm: data_get($response, '_primary_term')
        );
    }

    /**
     * @throws NotFoundResponseException
     * @throws UpdateResponseException
     * @throws ConflictResponseException
     */
    public function update(
        string $index,
        string|int $id,
        array $data,
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

        $response = Http::acceptJson()
            ->asJson()
            ->baseUrl(config('services.elasticsearch.url'))
            ->post($baseUrl, [
                'doc' => $data
            ]);

        if ($response->notFound() && data_get($response, 'status') === SymfonyResponse::HTTP_NOT_FOUND) {
            throw new NotFoundResponseException(
                json_encode($response->json()),
                $response->status()
            );
        }

        if (
            !is_null($primaryTerm)
            && !is_null($sequenceNumber)
            && $response->clientError()
            && data_get($response, 'status') === SymfonyResponse::HTTP_CONFLICT
        ) {
            throw new ConflictResponseException(
                json_encode($response->json()),
                $response->status()
            );
        }

        if ($response->clientError()) {
            throw new UpdateResponseException(
                json_encode($response->json()),
                $response->status()
            );
        }

        return new IndexResponseDto(
            index: data_get($response, '_index'),
            id: data_get($response, '_id'),
            version: data_get($response, '_version'),
            result: data_get($response, 'result'),
            shards: new ShardsResponseDto(
                total: data_get($response, '_shards.total'),
                successful: data_get($response, '_shards.successful'),
                failed: data_get($response, '_shards.failed'),
            ),
            sequenceNumber: data_get($response, '_seq_no'),
            primaryTerm: data_get($response, '_primary_term')
        );
    }

    /**
     * @throws DeleteResponseException
     * @throws NotFoundResponseException
     */
    public function delete(string $index, string|int $id): IndexResponseDto
    {
        $response = Http::acceptJson()
            ->asJson()
            ->baseUrl(config('services.elasticsearch.url'))
            ->delete("$index/_doc/$id");

        if ($response->notFound() && data_get($response, 'result') === 'not_found') {
            throw new NotFoundResponseException(
                json_encode($response->json()),
                $response->status()
            );
        }

        if ($response->clientError()) {
            throw new DeleteResponseException(
                json_encode($response->json()),
                $response->status()
            );
        }

        return new IndexResponseDto(
            index: data_get($response, '_index'),
            id: data_get($response, '_id'),
            version: data_get($response, '_version'),
            result: data_get($response, 'result'),
            shards: new ShardsResponseDto(
                total: data_get($response, '_shards.total'),
                successful: data_get($response, '_shards.successful'),
                failed: data_get($response, '_shards.failed'),
            ),
            sequenceNumber: data_get($response, '_seq_no'),
            primaryTerm: data_get($response, '_primary_term')
        );
    }
}
