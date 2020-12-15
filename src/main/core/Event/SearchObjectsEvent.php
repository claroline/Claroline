<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Event;

use Doctrine\ORM\QueryBuilder;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event dispatched when an object is searched inside the app.
 */
class SearchObjectsEvent extends Event
{
    /**
     * The pre-configured QueryBuilder for the search.
     *
     * @var QueryBuilder
     */
    private $queryBuilder;

    /**
     * The class of the objects currently searched.
     *
     * @var string
     */
    private $objectClass;

    /**
     * @var array
     */
    private $filters = [];

    /**
     * @var array
     */
    private $sortBy = null;

    /**
     * @var int
     */
    private $page = 0;

    /**
     * @var int
     */
    private $limit = -1;

    /**
     * SearchObjectEvent constructor.
     *
     * @param array $sortBy
     */
    public function __construct(
        QueryBuilder $queryBuilder,
        string $objectClass,
        array $filters = [],
        array $sortBy = null,
        int $page = 0,
        int $limit = -1
    ) {
        $this->queryBuilder = $queryBuilder;
        $this->objectClass = $objectClass;
        $this->filters = $filters;
        $this->sortBy = $sortBy;
        $this->page = $page;
        $this->limit = $limit;
    }

    /**
     * @return string
     */
    public function getObjectClass()
    {
        return $this->objectClass;
    }

    /**
     * Gets the query builder instance.
     *
     * @return QueryBuilder
     */
    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }

    /**
     * Get filters.
     *
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Set filters.
     */
    public function setFilters(array $filters)
    {
        $this->filters = $filters;
    }

    /**
     * Get sort by.
     *
     * @return array
     */
    public function getSortBy()
    {
        return $this->sortBy;
    }

    /**
     * Get page.
     *
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Get limit.
     *
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }
}
