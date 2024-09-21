<?php

namespace Claroline\AppBundle\Entity\Parameters;

use Doctrine\ORM\Mapping as ORM;

/**
 * Contains all properties required to configure an UI ListData.
 *
 * NB. maybe create an entity with this and create a rel in the entities using it.
 */
trait ListParameters
{
    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean')]
    protected $filterable = false;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean')]
    protected $sortable = false;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean')]
    protected $paginated = true;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean')]
    protected $columnsFilterable = false;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean')]
    protected $count = false;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean')]
    protected $actions = true;

    /**
     * @var string
     */
    #[ORM\Column(nullable: true)]
    protected $sortBy = null;

    /**
     * @var array
     */
    #[ORM\Column(type: 'json')]
    protected $availableSort = [];

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    protected $pageSize = 15;

    /**
     * @var array
     */
    #[ORM\Column(type: 'json')]
    protected $availablePageSizes = [15, 30, 60, 120, -1];

    /**
     * @var string
     */
    #[ORM\Column]
    protected $display = 'tiles-sm';

    /**
     * @var array
     */
    #[ORM\Column(type: 'json')]
    protected $availableDisplays = ['tiles-sm'];

    /**
     * @var string
     */
    #[ORM\Column(nullable: true)]
    protected $searchMode = null;

    /**
     * @var array
     */
    #[ORM\Column(type: 'json')]
    protected $filters = [];

    /**
     * @var array
     */
    #[ORM\Column(type: 'json')]
    protected $availableFilters = [];

    /**
     * @var array
     */
    #[ORM\Column(type: 'json')]
    protected $availableColumns = [];

    /**
     * @var array
     */
    #[ORM\Column(type: 'json')]
    protected $displayedColumns = [];

    /**
     * The configuration of the card.
     *
     *
     * @var array
     */
    #[ORM\Column(type: 'json')]
    protected $card = [];

    public function isFilterable(): bool
    {
        return $this->filterable;
    }

    public function setFilterable(bool $filterable)
    {
        $this->filterable = $filterable;
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    public function setSortable(bool $sortable)
    {
        $this->sortable = $sortable;
    }

    public function isPaginated(): bool
    {
        return $this->paginated;
    }

    public function setPaginated(bool $paginated)
    {
        $this->paginated = $paginated;
    }

    public function isColumnsFilterable(): bool
    {
        return $this->columnsFilterable;
    }

    public function setColumnsFilterable(bool $columnsFilterable)
    {
        $this->columnsFilterable = $columnsFilterable;
    }

    public function hasCount(): bool
    {
        return $this->count;
    }

    public function setCount(bool $count)
    {
        $this->count = $count;
    }

    public function hasActions(): bool
    {
        return $this->actions;
    }

    public function setActions(bool $actions)
    {
        $this->actions = $actions;
    }

    public function getSortBy(): ?string
    {
        return $this->sortBy;
    }

    public function setSortBy(string $sortBy = null)
    {
        $this->sortBy = $sortBy;
    }

    public function getAvailableSort(): array
    {
        return $this->availableSort ?? [];
    }

    public function setAvailableSort(array $availableSort)
    {
        $this->availableSort = $availableSort;
    }

    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    public function setPageSize(int $pageSize)
    {
        $this->pageSize = $pageSize;
    }

    public function getAvailablePageSizes(): array
    {
        return $this->availablePageSizes ?? [];
    }

    public function setAvailablePageSizes(array $availablePageSizes)
    {
        $this->availablePageSizes = $availablePageSizes;
    }

    public function getDisplay(): string
    {
        return $this->display;
    }

    public function setDisplay(string $display)
    {
        $this->display = $display;
    }

    public function getAvailableDisplays(): array
    {
        return $this->availableDisplays ?? [];
    }

    public function setAvailableDisplays(array $availableDisplays)
    {
        $this->availableDisplays = $availableDisplays;
    }

    public function getSearchMode(): ?string
    {
        return $this->searchMode;
    }

    public function setSearchMode(string $searchMode = null)
    {
        $this->searchMode = $searchMode;
    }

    public function getFilters(): array
    {
        return $this->filters ?? [];
    }

    public function setFilters(array $filters)
    {
        $this->filters = $filters;
    }

    public function getAvailableFilters(): array
    {
        return $this->availableFilters ?? [];
    }

    public function setAvailableFilters(array $availableFilters)
    {
        $this->availableFilters = $availableFilters;
    }

    public function getAvailableColumns(): array
    {
        return $this->availableColumns ?? [];
    }

    public function setAvailableColumns(array $availableColumns)
    {
        $this->availableColumns = $availableColumns;
    }

    public function getDisplayedColumns(): array
    {
        return $this->displayedColumns ?? [];
    }

    public function setDisplayedColumns(array $displayedColumns)
    {
        $this->displayedColumns = $displayedColumns;
    }

    public function getCard(): array
    {
        return $this->card ?? [];
    }

    public function setCard(array $card)
    {
        $this->card = $card;
    }
}
