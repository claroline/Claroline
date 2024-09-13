<?php

namespace Claroline\AppBundle\API\Finder;

use Doctrine\ORM\QueryBuilder;

/**
 * @deprecated
 */
interface FinderFilterInterface
{
    public function addFilter(QueryBuilder $qb, string $alias, ?array $searches = []): QueryBuilder;
}
