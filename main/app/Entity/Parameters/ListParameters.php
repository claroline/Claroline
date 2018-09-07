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
    private $filterable = false;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    private $sortable = false;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    private $paginated = false;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    private $columnsFilterable = false;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    private $count = false;

    /**
     * @ORM\Column(nullable=true)
     *
     * @var string
     */
    private $sortBy = null;

    /**
     * @ORM\Column(type="json_array")
     *
     * @var array
     */
    private $availableSort = [];

    /**
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    private $pageSize = 20;

    /**
     * @ORM\Column(type="json_array")
     *
     * @var array
     */
    private $availablePageSizes = [10, 20, 50, 100, -1];

    /**
     * @ORM\Column()
     *
     * @var array
     */
    private $display = 'tiles-sm';

    /**
     * @ORM\Column(type="json_array")
     *
     * @var array
     */
    private $availableDisplays = ['table', 'table-sm', 'tiles', 'tiles-sm', 'list'];

    /**
     * @ORM\Column(type="json_array")
     *
     * @var array
     */
    private $filters = [];

    /**
     * @ORM\Column(type="json_array")
     *
     * @var array
     */
    private $availableFilters = [];

    /**
     * @ORM\Column(type="json_array")
     *
     * @var array
     */
    private $availableColumns = [];

    /**
     * @ORM\Column(type="json_array")
     *
     * @var array
     */
    private $displayedColumns = [];

    /**
     * Is list filterable ?
     *
     * @return bool
     */
    public function isFilterable(): bool
    {
        return $this->filterable;
    }

    /**
     * Set list filterable.
     *
     * @param bool $filterable
     */
    public function setFilterable($filterable)
    {
        $this->filterable = $filterable;
    }

    /**
     * Is list sortable ?
     *
     * @return bool
     */
    public function isSortable(): bool
    {
        return $this->sortable;
    }

    /**
     * Set list sortable.
     *
     * @param bool $sortable
     */
    public function setSortable($sortable)
    {
        $this->sortable = $sortable;
    }

    /**
     * Is list paginated ?
     *
     * @return bool
     */
    public function isPaginated(): bool
    {
        return $this->paginated;
    }

    /**
     * Set list paginated.
     *
     * @param $paginated
     */
    public function setPaginated($paginated)
    {
        $this->paginated = $paginated;
    }

    /**
     * @return bool
     */
    public function isColumnsFilterable()
    {
        return $this->columnsFilterable;
    }

    /**
     * @param bool $columnsFilterable
     */
    public function setColumnsFilterable($columnsFilterable)
    {
        $this->columnsFilterable = $columnsFilterable;
    }

    /**
     * @return bool
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @param bool $count
     */
    public function setCount($count)
    {
        $this->count = $count;
    }

    /**
     * Get sort by.
     *
     * @return string
     */
    public function getSortBy()
    {
        return $this->sortBy;
    }

    /**
     * Set sort by.
     *
     * @param string $sortBy
     */
    public function setSortBy($sortBy)
    {
        $this->sortBy = $sortBy;
    }

    /**
     * Get sortable columns.
     *
     * @return array
     */
    public function getAvailableSort()
    {
        return $this->availableSort;
    }

    /**
     * Set sortable columns.
     *
     * @param array $availableSort
     */
    public function setAvailableSort(array $availableSort)
    {
        $this->availableSort = $availableSort;
    }

    /**
     * Get page size.
     *
     * @return int
     */
    public function getPageSize()
    {
        return $this->pageSize;
    }

    /**
     * Set page size.
     *
     * @param int $pageSize
     */
    public function setPageSize($pageSize)
    {
        $this->pageSize = $pageSize;
    }

    /**
     * Get display.
     *
     * @return string
     */
    public function getDisplay()
    {
        return $this->display;
    }

    /**
     * Get available page sizes.
     *
     * @return array
     */
    public function getAvailablePageSizes()
    {
        return $this->availablePageSizes;
    }

    /**
     * Set available page sizes.
     *
     * @param array $availablePageSizes
     */
    public function setAvailablePageSizes(array $availablePageSizes)
    {
        $this->availablePageSizes = $availablePageSizes;
    }

    /**
     * Set display.
     *
     * @param $display
     */
    public function setDisplay($display)
    {
        $this->display = $display;
    }

    /**
     * Get available displays.
     *
     * @return array
     */
    public function getAvailableDisplays()
    {
        return $this->availableDisplays;
    }

    /**
     * Set available displays.
     *
     * @param array $availableDisplays
     */
    public function setAvailableDisplays(array $availableDisplays)
    {
        $this->availableDisplays = $availableDisplays;
    }

    /**
     * Get default filters.
     *
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Set default filters.
     *
     * @param array $filters
     */
    public function setFilters(array $filters)
    {
        $this->filters = $filters;
    }

    /**
     * Get available filters.
     *
     * @return array
     */
    public function getAvailableFilters()
    {
        return $this->availableFilters;
    }

    /**
     * Set available filters.
     *
     * @param array $availableFilters
     */
    public function setAvailableFilters(array $availableFilters)
    {
        $this->availableFilters = $availableFilters;
    }

    /**
     * Get available columns.
     *
     * @return array
     */
    public function getAvailableColumns()
    {
        return $this->availableColumns;
    }

    /**
     * Set available columns.
     *
     * @param array $availableColumns
     */
    public function setAvailableColumns(array $availableColumns)
    {
        $this->availableColumns = $availableColumns;
    }

    /**
     * Get displayed columns.
     *
     * @return array
     */
    public function getDisplayedColumns()
    {
        return $this->displayedColumns;
    }

    /**
     * Set displayed columns.
     *
     * @param array $displayedColumns
     */
    public function setDisplayedColumns(array $displayedColumns)
    {
        $this->displayedColumns = $displayedColumns;
    }
}
