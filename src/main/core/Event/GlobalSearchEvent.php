<?php

namespace Claroline\CoreBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class GlobalSearchEvent extends Event
{
    /** @var string */
    private $search;
    /** @var int */
    private $limit;
    /** @var array */
    private $searchableItems;

    /** @var array */
    private $results = [];

    public function __construct(string $search, int $limit, array $searchableItems)
    {
        $this->search = $search;
        $this->limit = $limit;
        $this->searchableItems = $searchableItems;
    }

    public function getSearch(): string
    {
        return $this->search;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function includeItems(string $itemName): bool
    {
        return in_array($itemName, $this->searchableItems);
    }

    public function getResults(): array
    {
        return $this->results;
    }

    public function addResults(string $itemName, array $results): void
    {
        $this->results[$itemName] = $results;
    }
}
