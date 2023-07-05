<?php

namespace Olekjs\Elasticsearch\Bulk;

use JsonException;
use LogicException;
use Olekjs\Elasticsearch\Contracts\BulkOperationInterface;

class Bulk implements BulkOperationInterface
{
    private const UPDATE_ACTION = 'update';

    private array $documents;

    /*
     * @throws LogicException
     */
    public function add(string $action, string $index, string $id, ?array $data = []): self
    {
        $this->addDocument($action, $index, $id, $data);

        return $this;
    }

    /*
     * @throws LogicException
     */
    public function addMany(array $documents): self
    {
        foreach ($documents as $document) {
            $this->validateDocument($document);
            $this->addDocument($document['action'], $document['index'], $document['id'], $document['data']);
        }

        return $this;
    }

    public function getDocuments(): array
    {
        return $this->documents;
    }

    /**
     * @throws JsonException
     */
    public function toRequestJson(): string
    {
        $body = [];

        foreach ($this->getDocuments() as $document) {
            if (empty($document)) {
                continue;
            }

            $body[] = json_encode($document, JSON_THROW_ON_ERROR);
        }

        return join(PHP_EOL, $body) . PHP_EOL;
    }

    /*
     * @throws LogicException
     */
    public function validateDocument(array $document): void
    {
        if (
            !isset($document['action'], $document['index'], $document['id'], $document['data'])
            || !is_string($document['action'])
            || !(is_string($document['index']) || is_int($document['index']))
            || !(is_string($document['id']) || is_int($document['id']))
            || !is_array($document['data'])
        ) {
            throw new LogicException('Document has incorrect structure.');
        }
    }

    protected function addDocument(string $action, string $index, string $id, array $data): void
    {
        $this->documents[][$action] = ['_index' => $index, '_id' => $id];

        if (empty($data)) {
            return;
        }

        match ($action) {
            self::UPDATE_ACTION => $this->documents[]['doc'] = $data,
            default => $this->documents[] = $data,
        };
    }
}
