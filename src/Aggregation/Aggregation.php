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
        $request = [
            $this->name => $this->data,
        ];

        if (!empty($this->subAggregations)) {
            foreach ($this->subAggregations as $subAggregation) {
                $request[$this->name]['aggs'][$subAggregation->getName()] = $this->handleSubAggregationData($subAggregation);
            }
        }

        return $request;
    }

    private function handleSubAggregationData(Aggregation $aggregation): array
    {
        $request = $aggregation->getData();

        if (!empty($aggregation->subAggregations)) {
            foreach ($aggregation->subAggregations as $subAggregation) {
                $subAggregationData = $subAggregation->getData();

                $request['aggs'][$subAggregation->getName()] = $subAggregationData;

                if (!empty($subAggregation->getSubAggregations())) {
                    $subAggregationData['aggs'] = $this->handleSubAggregationData($subAggregation);
                }
            }
        }

        return $request;
    }
}
