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
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    protected $filterable = false;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    protected $sortable = false;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    protected $paginated = true;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    protected $columnsFilterable = false;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    protected $count = false;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    protected $actions = true;

    /**
     * @ORM\Column(nullable=true)
     *
     * @var string
     */
    protected $sortBy = null;

    /**
     * @ORM\Column(type="json")
     *
     * @var array
     */
    protected $availableSort = [];

    /**
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    protected $pageSize = 15;

    /**
     * @ORM\Column(type="json")
     *
     * @var array
     */
    protected $availablePageSizes = [15, 30, 60, 120, -1];

    /**
     * @ORM\Column()
     *
     * @var string
     */
    protected $display = 'tiles-sm';

    /**
     * @ORM\Column(type="json")
     *
     * @var array
     */
    protected $availableDisplays = ['tiles-sm'];

    /**
     * @ORM\Column(nullable=true)
     *
     * @var string
     */
    protected $searchMode = null;

    /**
     * @ORM\Column(type="json")
     *
     * @var array
     */
    protected $filters = [];

    /**
     * @ORM\Column(type="json")
     *
     * @var array
     */
    protected $availableFilters = [];

    /**
     * @ORM\Column(type="json")
     *
     * @var array
     */
    protected $availableColumns = [];

    /**
     * @ORM\Column(type="json")
     *
     * @var array
     */
    protected $displayedColumns = [];

    /**
     * The configuration of the card.
     *
     * @ORM\Column(type="json")
     *
     * @var array
     */
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
        return $this->availableSort;
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
        //in case it was null after migration or bug
        if (!$this->availablePageSizes) {
            return [];
        }

        return $this->availablePageSizes;
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
        //in case it was null after migration or bug

        if (!$this->availableDisplays) {
            return [];
        }

        return $this->availableDisplays;
    }

    public function setAvailableDisplays(array $availableDisplays)
    {
        $this->availableDisplays = $availableDisplays;
    }

    public function getSearchMode(): string
    {
        return $this->searchMode;
    }

    public function setSearchMode(string $searchMode)
    {
        $this->searchMode = $searchMode;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function setFilters(array $filters)
    {
        $this->filters = $filters;
    }

    public function getAvailableFilters(): array
    {
        //in case it was null after migration or bug
        if (!$this->availableFilters) {
            return [];
        }

        return $this->availableFilters;
    }

    public function setAvailableFilters(array $availableFilters)
    {
        $this->availableFilters = $availableFilters;
    }

    public function getAvailableColumns(): array
    {
        //in case it was null after migration or bug
        if (!$this->availableColumns) {
            return [];
        }

        return $this->availableColumns;
    }

    public function setAvailableColumns(array $availableColumns)
    {
        $this->availableColumns = $availableColumns;
    }

    public function getDisplayedColumns(): array
    {
        //in case it was null after migration or bug
        if (!$this->displayedColumns) {
            return [];
        }

        return $this->displayedColumns;
    }

    public function setDisplayedColumns(array $displayedColumns)
    {
        $this->displayedColumns = $displayedColumns;
    }

    public function getCard(): array
    {
        //in case it was null after migration or bug
        if (!$this->card) {
            return [];
        }

        return $this->card;
    }

    public function setCard(array $card)
    {
        $this->card = $card;
    }
}
