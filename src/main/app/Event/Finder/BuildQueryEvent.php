<?php

namespace Claroline\AppBundle\Event\Finder;

use Claroline\AppBundle\API\Finder\FinderInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Contracts\EventDispatcher\Event;

final class BuildQueryEvent extends Event
{
    public function __construct(
        private readonly QueryBuilder $queryBuilder,
        private readonly FinderInterface $finder,
        private readonly array $options
    ) {
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }

    public function getFinder(): FinderInterface
    {
        return $this->finder;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
