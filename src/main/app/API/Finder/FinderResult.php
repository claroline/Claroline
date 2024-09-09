<?php

namespace Claroline\AppBundle\API\Finder;

use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;

class FinderResult
{
    private Paginator $paginator;

    public function __construct(
        Query $query
    ) {
        $this->paginator = new Paginator($query/* , false */);
    }

    public function count(): int
    {
        return count($this->paginator);
    }

    public function get(): iterable
    {
        return $this->paginator
            ->getQuery()
            ->toIterable();
    }
}
