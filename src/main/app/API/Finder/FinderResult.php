<?php

namespace Claroline\AppBundle\API\Finder;

use Closure;
use Countable;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class FinderResult implements FinderResultInterface, Countable
{
    private ?int $count = null;
    private ?iterable $results = null;

    public function __construct(
        private readonly string $name,
        private readonly FinderQuery $searchQuery,
        private readonly QueryBuilder $queryBuilder,
        private readonly ?Closure $rowTransformer = null
    ) {
    }

    public function count(): int
    {
        if (null === $this->count) {
            $this->count = $this->getCountQuery()
                ->getSingleScalarResult();
        }

        return $this->count;
    }

    public function getItems(): iterable
    {
        if (null === $this->results) {
            if (0 < $this->searchQuery->getPageSize()) {
                $this->queryBuilder->setFirstResult($this->searchQuery->getPage() * $this->searchQuery->getPageSize());
                $this->queryBuilder->setMaxResults($this->searchQuery->getPageSize());
            }

            $this->results = $this->queryBuilder
                ->getQuery()
                ->toIterable();
        }

        if (null !== $this->rowTransformer) {
            foreach ($this->results as $result) {
                yield ($this->rowTransformer)($result);
            }
        }

        return $this->results;
    }

    private function getCountQuery(): Query
    {
        $countQueryBuilder = clone $this->queryBuilder;

        return $countQueryBuilder
            ->select($countQueryBuilder->getDQLPart('distinct') ?
                $countQueryBuilder->expr()->countDistinct($this->name) :
                $countQueryBuilder->expr()->count($this->name)
            )
            ->distinct(false)
            ->setFirstResult(0)
            ->setMaxResults(null)
            ->getQuery();
    }

    /**
     * @deprecated dev only
     */
    public function getQuery(): Query
    {
        return $this->queryBuilder->getQuery();
    }
}
