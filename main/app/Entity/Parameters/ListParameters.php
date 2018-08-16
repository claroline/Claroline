<?php

namespace Claroline\AppBundle\Entity\Parameters;

use Doctrine\ORM\Mapping as ORM;

trait ListParameters
{
    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    private $filterable = true;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    private $sortable = true;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    private $paginated = true;

    /**
     * @ORM\Column(nullable=true)
     *
     * @var string
     */
    private $sortBy = null;

    /**
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    private $pageSize = 20;

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
    public function isFilterable()
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
    public function isSortable()
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
    public function isPaginated()
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
     * @return array
     */
    public function getDisplay()
    {
        return $this->display;
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
     * Set available display.
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
     * Get available columns.
     *
     * @return array
     */
    public function getAvailableColumns()
    {
        return $this->availableColumns;
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
}
