<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AppBundle\API\Finder;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Event\SearchObjectsEvent;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @deprecated
 */
abstract class AbstractFinder
{
    protected ObjectManager $om;
    protected EventDispatcherInterface $eventDispatcher;
    private iterable $filters;

    /**
     * @internal used by DI
     */
    public function setObjectManager(ObjectManager $om): void
    {
        $this->om = $om;
    }

    /**
     * @internal used by DI
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @internal used by DI
     */
    public function setFilters(iterable $filters): void
    {
        $this->filters = $filters;
    }

    /**
     * The queried object is already named "obj".
     */
    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, ?int $page = 0, ?int $limit = -1): QueryBuilder
    {
        foreach ($searches as $filterName => $filterValue) {
            $this->setDefaults($qb, $filterName, $filterValue);
        }

        return $qb;
    }

    public function find(?array $filters = [], array $sortBy = null, ?int $page = 0, ?int $limit = -1, ?bool $count = false): int|array
    {
        // sorting is not required when we count stuff
        $sortBy = $count ? null : $sortBy;

        $qb = $this->om->createQueryBuilder();
        $qb->select($count ? 'COUNT(DISTINCT obj)' : 'DISTINCT obj')->from(static::getClass(), 'obj');

        // Lets the whole app knows we are doing a search with an event
        // ATTENTION : This needs to be done first because if a listener manage a filter (like Tags),
        // it needs to be removed from list of filters to avoid the finder implementation to process it
        $event = new SearchObjectsEvent($qb, static::getClass(), 'obj', $filters, $sortBy, $page, $limit);
        $this->eventDispatcher->dispatch($event, 'objects.search');

        // filter query - lets the finder implementation process the filters to configure query
        $qb = $this->configureQueryBuilder($qb, $event->getFilters(), $sortBy, $page, $limit);

        // order query if implementation has not done it
        $this->sortResults($qb, $sortBy);
        if (!$count && 0 < $limit) {
            $qb->setFirstResult($page * $limit);
            $qb->setMaxResults($limit);
        }

        $query = $qb->getQuery();

        return $count ? (int) $query->getSingleScalarResult() : $query->getResult();
    }

    protected function addFilter(string $handlerClass, QueryBuilder $qb, string $alias, ?array $searches = []): QueryBuilder
    {
        $handler = null;
        foreach ($this->filters as $filterDef) {
            if ($filterDef instanceof $handlerClass) {
                $handler = $filterDef;
                break;
            }
        }

        if (!$handler) {
            throw new \Exception(sprintf('Request an unknown filter handler %s.', $handlerClass));
        }

        $handler->addFilter($qb, $alias, $searches);

        return $qb;
    }

    protected function addSort(string $handlerClass, QueryBuilder $qb, string $alias, string $sortBy, string $direction): QueryBuilder
    {
        $handler = null;
        foreach ($this->filters as $filterDef) {
            if ($filterDef instanceof $handlerClass) {
                $handler = $filterDef;
                break;
            }
        }

        if (!$handler) {
            throw new \Exception(sprintf('Request an unknown sort handler %s.', $handlerClass));
        }

        $handler->addSort($qb, $alias, $sortBy, $direction);

        return $qb;
    }

    protected function setDefaults(QueryBuilder $qb, string $filterName, mixed $filterValue): void
    {
        $property = $filterName;
        if (array_key_exists($filterName, $this->getExtraFieldMapping())) {
            $property = $this->getExtraFieldMapping()[$filterName];
        }

        if (!property_exists(static::getClass(), $property)) {
            return;
        }

        if (is_string($filterValue)) {
            $qb->andWhere("UPPER(obj.$property) LIKE :$property");
            $qb->setParameter($property, '%'.strtoupper($filterValue).'%');
        } elseif (is_array($filterValue)) {
            $qb->andWhere("obj.$property IN (:$property)");
            $qb->setParameter($property, $filterValue);
        } else {
            $qb->andWhere("obj.$property = :$property");
            $qb->setParameter($property, $filterValue);
        }
    }

    private function sortResults(QueryBuilder $qb, array $sortBy = null): void
    {
        if ($sortBy && $sortBy['property'] && 0 !== $sortBy['direction']) {
            // query needs to be sorted, check if the Finder implementation has a custom sort system
            $queryOrder = $qb->getDQLPart('orderBy');
            if (!$queryOrder) {
                // no order by defined
                $property = $sortBy['property'];
                if (array_key_exists($sortBy['property'], $this->getExtraFieldMapping())) {
                    $property = $this->getExtraFieldMapping()[$sortBy['property']];
                }

                if (!property_exists(static::getClass(), $property)) {
                    return;
                }

                $qb->orderBy('obj.'.$property, 1 === $sortBy['direction'] ? 'ASC' : 'DESC');
            }
        }
    }

    protected function getExtraFieldMapping(): array
    {
        return [];
    }

    /**
     * Gets the list of filters managed by the Finder.
     * It is used by the auto-documentation for API and tests.
     */
    public function getFilters(): array
    {
        return [
            // some black magic here : it will read annotations on Entity to retrieve all props defined.
            '$defaults' => [],
        ];
    }
}
