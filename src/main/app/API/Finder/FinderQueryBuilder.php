<?php

namespace Claroline\AppBundle\API\Finder;

use Claroline\CoreBundle\Event\SearchObjectsEvent;
use Doctrine\ORM\Query;

class FinderQueryBuilder
{
    public function getQuery(FinderQuery $query): Query
    {
        $qb = $this->om->createQueryBuilder()
            ->select('obj')
            ->distinct()
            ->from(static::getClass(), 'obj');
        // $qb->select($count ? 'COUNT(DISTINCT obj)' : 'DISTINCT obj')->from(static::getClass(), 'obj');

        // Lets the whole app knows we are doing a search with an event
        // ATTENTION : This needs to be done first because if a listener manage a filter (like Tags),
        // it needs to be removed from list of filters to avoid the finder implementation to process it
        $event = new SearchObjectsEvent($qb, static::getClass(), 'obj', $query->getFilters(), $query->getSortBy(), $query->getPage(), $query->getPageSize());
        $this->eventDispatcher->dispatch($event, 'objects.search');

        // filter query - lets the finder implementation process the filters to configure query
        $qb = $this->configureQueryBuilder($qb, $event->getFilters(), $query->getSortBy(), $query->getPage(), $query->getPageSize());

        // order query if implementation has not done it
        // $this->sortResults($qb, $sortBy);
        if (0 < $query->getPageSize()) {
            $qb->setFirstResult($query->getPage() * $query->getPageSize());
            $qb->setMaxResults($query->getPageSize());
        }

        return $qb->getQuery();
    }
}
