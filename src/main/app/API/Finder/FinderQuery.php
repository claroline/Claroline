<?php

namespace Claroline\AppBundle\API\Finder;

use Symfony\Component\HttpFoundation\Request;

class FinderQuery
{
    public const ALL = -1;

    private ?string $search;
    private array $filters;
    private array $sortBy;
    private int $page;
    private int $pageSize;

    public function __construct(string $search = null, array $filters = [], array $sortBy = [], ?int $page = 0, ?int $pageSize = self::ALL)
    {
        $this->search = $search;
        $this->filters = $filters;
        $this->sortBy = $sortBy;
        $this->page = $page;
        $this->pageSize = $pageSize;
    }

    /**
     * Build a FinderQuery from an HTTP request.
     */
    public static function fromRequest(Request $request): self
    {
        $query = $request->query->all();

        return new self(
            !empty($query['q']) ? $query['q'] : null,
            !empty($query['filters']) ? $query['filters'] : [],
            !empty($query['sortBy']) ? $query['sortBy'] : [],
            !empty($query['page']) ? (int) $query['page'] : 0,
            !empty($query['limit']) ? (int) $query['limit'] : self::ALL
        );
    }

    public function getSearch(): ?string
    {
        return $this->search;
    }

    public function setSearch(?string $search): self
    {
        $this->search = $search;

        return $this;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function setPage(int $page): self
    {
        $this->page = $page;

        return $this;
    }

    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    public function setPageSize(int $pageSize): self
    {
        $this->pageSize = $pageSize;

        return $this;
    }

    public function getSortBy(): array
    {
        return $this->sortBy;
    }

    public function getSort(string $sortName): mixed
    {
        if (array_key_exists($sortName, $this->sortBy)) {
            return $this->sortBy[$sortName];
        }

        return null;
    }

    public function addSorts(array $sort): self
    {
        foreach ($sort as $sortName => $sortDirection) {
            $this->addSort($sortName, $sortDirection);
        }

        return $this;
    }

    public function addSort(string $propName, string $sortDirection): void
    {
        $this->sortBy[$propName] = $sortDirection;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function getFilter(string $filterName): mixed
    {
        if (array_key_exists($filterName, $this->filters)) {
            return $this->filters[$filterName];
        }

        return null;
    }

    public function addFilters(array $filters): self
    {
        foreach ($filters as $filterName => $filterValue) {
            $this->addFilter($filterName, $filterValue);
        }

        return $this;
    }

    public function addFilter(string $filterName, mixed $filterValue): self
    {
        $this->filters[$filterName] = $filterValue;

        return $this;
    }
}
