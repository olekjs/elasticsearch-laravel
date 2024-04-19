<?php

namespace Olekjs\Elasticsearch\Alias;

use Illuminate\Http\Client\Response;
use Olekjs\Elasticsearch\Client;
use Olekjs\Elasticsearch\Contracts\AliasInterface;
use Olekjs\Elasticsearch\Contracts\ClientInterface;
use Olekjs\Elasticsearch\Exceptions\SearchResponseException;

class Alias implements AliasInterface
{
    public function __construct(private readonly ClientInterface $client = new Client())
    {
    }

    /**
     * @throws SearchResponseException
     */
    public function getIndicesForAlias(string $alias): array
    {
        $response = $this->client->getBaseClient()->get("$alias/_alias");

        if ($response->clientError()) {
            $this->client->throwSearchResponseException(
                data_get($response, 'error.reason'),
                $response->status(),
            );
        }

        $indices = [];
        foreach ($response->json() as $index => $aliases) {
            $indices[] = $index;
        }

        return $indices;
    }

    public function add(string $index, string $alias): bool
    {
        $response = $this->runActions([
            [
                'add' => [
                    'index' => $index,
                    'alias' => $alias,
                ]
            ]
        ]);

        return $response->successful();
    }

    public function remove(string $index, string $alias): bool
    {
        $response = $this->runActions([
            [
                'remove' => [
                    'index' => $index,
                    'alias' => $alias,
                ]
            ]
        ]);

        return $response->successful();
    }

    public function runActions(array $actions): Response
    {
        $response = $this->client->getBaseClient()->post('_aliases', ['actions' => $actions]);

        if ($response->clientError()) {
            $this->client->throwUpdateResponseException(
                json_encode($response->json(), JSON_THROW_ON_ERROR),
                $response->status()
            );
        }

        return $response;
    }

    public function replace(string $alias, string $newIndex, ?string $oldIndex = null): bool
    {
        if (null === $oldIndex) {
            $indices = $this->getIndicesForAlias($alias);

            $oldIndex = $indices[0] ?? null;
        }

        if (null === $oldIndex) {
            throw new \LogicException('Old index is not defined.');
        }

        $response = $this->runActions([
            [
                'add' => [
                    'index' => $newIndex,
                    'alias' => $alias,
                ]
            ],
            [
                'remove' => [
                    'index' => $oldIndex,
                    'alias' => $alias,
                ]
            ]
        ]);

        return $response->successful();
    }
}