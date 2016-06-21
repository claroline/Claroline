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

use Symfony\Component\EventDispatcher\Event;

class UserAddFilterEvent extends Event
{
    private $filters;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function addFilter($filter)
    {
        $this->filters[] = $filter;
    }

    public function removeFilter($filter)
    {
        if (($key = array_search($filter, $this->filters)) !== false) {
            unset($filters[$key]);
        }
    }

    public function getFilters()
    {
        return $this->filters;
    }

    public function setFilters($filters)
    {
        $this->filters = $filters;
    }
}
