<?php

namespace Claroline\AppBundle\API\Finder;

use Doctrine\ORM\QueryBuilder;

interface FinderFilterInterface
{
    public function addFilter(QueryBuilder $qb, string $alias, ?array $searches = []): QueryBuilder;

    public function addSort(QueryBuilder $qb, string $alias, string $sortBy, string $direction): QueryBuilder;
}
