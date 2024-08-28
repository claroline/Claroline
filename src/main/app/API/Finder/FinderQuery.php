<?php

namespace Claroline\AppBundle\API\Finder;

use Symfony\Component\HttpFoundation\Request;

class FinderQuery
{
    private ?string $fulltext;
    private array $filters;
    private array $sortBy;
    private int $page;
    private int $pageSize;


    public function __construct(string $fulltext = null, array $filters = [], array $sortBy = null, ?int $page = 0, ?int $pageSize = -1)
    {
        $this->fulltext = $fulltext;
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
            !empty($query['filters']) ? self::parseFilters($query['filters']) : [],
            !empty($query['sortBy']) ? self::parseSortBy($query['sortBy']) : [],
            !empty($query['page']) ? $query['page'] : 0,
            !empty($query['pageSize']) ? $query['pageSize'] : -1
        );
    }

    public function getFulltext(): ?string
    {
        return $this->fulltext;
    }

    public function setFulltext(?string $fulltext): self
    {
        $this->fulltext = $fulltext;

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

    public function setSortBy(string $propName, string $sortDirection): void
    {
        $this->sortBy = [$propName, $sortDirection];
    }

    public function getFilters(): array
    {
        return $this->filters;
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

    private static function parseFilters(array $filters): array
    {
        $parsed = [];
        foreach ($filters as $property => $value) {
            // don't keep empty filters
            if ('' !== $value) {
                if (null !== $value) {
                    // parse filter value
                    if (is_numeric($value)) {
                        // convert numbers
                        $floatValue = floatval($value);
                        if ($value === $floatValue.'') {
                            // dumb check to allow users search with strings like '001' without catching it as a number
                            $value = $floatValue;
                        }
                    } else {
                        // convert booleans
                        $booleanValue = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                        if (null !== $booleanValue) {
                            $value = $booleanValue;
                        }
                    }
                }

                $parsed[$property] = $value;
            }
        }

        return $parsed;
    }

    private static function parseSortBy(?string $sortBy): ?array
    {
        // default values
        $property = null;
        $direction = null;

        if (!empty($sortBy)) {
            if (str_starts_with($sortBy, '-')) {
                $property = substr($sortBy, 1);
                $direction = 'DESC';
            } else {
                $property = $sortBy;
                $direction = 'ASC';
            }
        }

        if ($property && $direction) {
            return [$property, $direction];
        }

        return null;
    }
}