<?php

namespace Olekjs\Elasticsearch\Dto;

use Illuminate\Contracts\Support\Arrayable;
use Olekjs\Elasticsearch\Contracts\ResponseDtoInterface;

class PaginateResponseDto implements ResponseDtoInterface, Arrayable
{
    public function __construct(
        private readonly int $perPage,
        private readonly int $currentPage,
        private readonly int $totalPages,
        private readonly int $totalDocuments,
        private readonly SearchResponseDto $documents,
    ) {
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function getDocuments(): SearchResponseDto
    {
        return $this->documents;
    }

    public function getTotalDocuments(): int
    {
        return $this->totalDocuments;
    }

    public function getTotalPages(): int
    {
        return $this->totalPages;
    }

    public function toArray(): array
    {
        return [
            'per_page' => $this->getPerPage(),
            'current_page' => $this->getCurrentPage(),
            'total_pages' => $this->getTotalPages(),
            'total_documents' => $this->getTotalDocuments(),
            'documents' => $this->getDocuments()->toArray(),
        ];
    }
}
