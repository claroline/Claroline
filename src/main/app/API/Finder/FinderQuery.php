<?php

namespace Claroline\AppBundle\API\Finder;

use Symfony\Component\HttpFoundation\Request;

/**
 * DTO which represents o user search request.
 *
 * It can be automatically filled with the Request query string :
 *
 *      public function controllerAction(
 *          #[MapQueryString]
 *          ?FinderQuery $finderQuery = new FinderQuery()
 *      )
 */
class FinderQuery
{
    public const SORT_ASC = 'ASC';
    public const SORT_DESC = 'DESC';
    public const ALL = -1;

    private ?string $search;
    private array $filters;
    private array $sortBy;
    private int $page;
    private int $pageSize;

    public function __construct(string $q = null, array $filters = [], array $sortBy = [], ?int $page = 0, ?int $limit = self::ALL)
    {
        $this->search = $q;
        $this->filters = $filters;
        $this->sortBy = $sortBy;
        $this->page = $page;
        $this->pageSize = $limit;
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

    public function getSorts(): array
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

    public function addSorts(array $sort, ?bool $overrideExistingValue = true): self
    {
        foreach ($sort as $sortName => $sortDirection) {
            $this->addSort($sortName, $sortDirection, $overrideExistingValue);
        }

        return $this;
    }

    public function addSort(string $propName, string $sortDirection = self::SORT_ASC, ?bool $overrideExistingValue = true): self
    {
        if ($overrideExistingValue || !array_key_exists($propName, $this->sortBy)) {
            $this->sortBy[$propName] = $sortDirection;
        }

        return $this;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function hasFilter(string $filterName): bool
    {
        return array_key_exists($filterName, $this->filters);
    }

    public function getFilter(string $filterName): mixed
    {
        if (array_key_exists($filterName, $this->filters)) {
            return $this->filters[$filterName];
        }

        return null;
    }

    public function addFilters(array $filters, ?bool $overrideExistingValue = true): self
    {
        foreach ($filters as $filterName => $filterValue) {
            $this->addFilter($filterName, $filterValue, $overrideExistingValue);
        }

        return $this;
    }

    public function addFilter(string $filterName, mixed $filterValue, ?bool $overrideExistingValue = true): self
    {
        if ($overrideExistingValue || !array_key_exists($filterName, $this->filters)) {
            $this->filters[$filterName] = $filterValue;
        }

        return $this;
    }
}
