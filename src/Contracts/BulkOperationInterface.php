<?php

namespace Olekjs\Elasticsearch\Contracts;

use JsonException;

interface BulkOperationInterface
{
    /*
      * @throws LogicException
      */
    public function add(string $action, string $index, string $id, ?array $data = []): self;

    /*
     * @throws LogicException
     */
    public function addMany(array $documents): self;

    public function getDocuments(): array;

    /**
     * @throws JsonException
     */
    public function toRequestJson(): string;

    /*
     * @throws LogicException
     */
    public function validateDocument(array $document): void;
}
