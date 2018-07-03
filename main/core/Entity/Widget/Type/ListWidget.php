<?php

namespace Claroline\CoreBundle\Entity\Widget\Type;

use Doctrine\ORM\Mapping as ORM;

/**
 * ListWidget.
 *
 * Permits to render an arbitrary list of data.
 *
 * @ORM\Entity()
 * @ORM\Table(name="claro_widget_list")
 */
class ListWidget extends AbstractWidget
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
    private $defaultFilters = [];

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
    public function getDefaultFilters()
    {
        return $this->defaultFilters;
    }

    /**
     * Set default filters.
     *
     * @param array $defaultFilters
     */
    public function setDefaultFilters(array $defaultFilters)
    {
        $this->defaultFilters = $defaultFilters;
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
