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
    public function __construct(
        private readonly QueryBuilder $queryBuilder,
        private readonly string $objectClass,
        private readonly string $objectAlias = 'obj',
        private array $filters = [],
        private readonly ?array $sortBy = null,
        private readonly int $page = 0,
        private readonly int $limit = -1
    ) {
    }

    public function getObjectClass(): string
    {
        return $this->objectClass;
    }

    public function getObjectAlias(): string
    {
        return $this->objectAlias;
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function setFilters(array $filters): void
    {
        $this->filters = $filters;
    }

    public function getSortBy(): ?array
    {
        return $this->sortBy;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }
}
