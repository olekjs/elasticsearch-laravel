<?php

namespace Olekjs\Elasticsearch\Contracts;

use Illuminate\Support\Collection;

interface Collectionable
{
    public function toCollect(): Collection;
}
