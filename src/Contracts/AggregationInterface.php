<?php

namespace Olekjs\Elasticsearch\Contracts;

interface AggregationInterface
{
    public function getName(): string;

    public function getData(): array;

    public function getSubAggregations(): array;

    public function addSubAggregation(AggregationInterface $subAggregation): self;

    public function toRequestArray(): array;
}
