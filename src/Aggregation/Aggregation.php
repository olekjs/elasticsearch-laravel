<?php

namespace Olekjs\Elasticsearch\Aggregation;

use Olekjs\Elasticsearch\Contracts\AggregationInterface;

class Aggregation implements AggregationInterface
{
    public function __construct(
        private readonly string $name,
        private readonly array $data,

        /** @var AggregationInterface[] $subAggregations */
        private array $subAggregations = [],
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getSubAggregations(): array
    {
        return $this->subAggregations;
    }

    public function addSubAggregation(AggregationInterface $subAggregation): self
    {
        $this->subAggregations[] = $subAggregation;

        return $this;
    }

    public function toRequestArray(): array
    {
        $aggregationArray = [
            $this->name => $this->data
        ];

        foreach ($this->subAggregations as $subAggregation) {
            $aggregationArray[$this->name]['aggs'] = array_merge(
                $aggregationArray[$this->name]['aggs'] ?? [],
                $subAggregation->toRequestArray()
            );
        }

        return $aggregationArray;
    }
}
