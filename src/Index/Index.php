<?php

namespace Olekjs\Elasticsearch\Index;

use Olekjs\Elasticsearch\Client;
use Olekjs\Elasticsearch\Contracts\ClientInterface;
use Olekjs\Elasticsearch\Contracts\IndexInterface;
use Olekjs\Elasticsearch\Exceptions\UpdateResponseException;

class Index implements IndexInterface
{
    public function __construct(private readonly ClientInterface $client = new Client())
    {
    }

    /**
     * @throws UpdateResponseException
     * @throws \JsonException
     */
    public function create(string $name, array $settings = []): bool
    {
        $response = $this->client->getBaseClient()->put($name, (object) $settings);

        if ($response->clientError()) {
            $this->client->throwUpdateResponseException(
                json_encode($response->json(), JSON_THROW_ON_ERROR),
                $response->status()
            );
        }

        return $response->successful();
    }

    public function delete(string $name): bool
    {
        $response = $this->client->getBaseClient()->delete($name);

        if ($response->clientError()) {
            $this->client->throwDeleteResponseException(
                json_encode($response->json(), JSON_THROW_ON_ERROR),
                $response->status()
            );
        }

        return $response->successful();
    }
}