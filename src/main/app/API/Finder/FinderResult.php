<?php

namespace Claroline\AppBundle\API\Finder;

use Doctrine\ORM\Query;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;

class FinderResult implements FinderResultInterface, \Countable
{
    private ?int $count = null;
    private ?iterable $results = null;

    public function __construct(
        private readonly string $name,
        private readonly FinderQuery $searchQuery,
        private readonly QueryBuilder $queryBuilder,
        private readonly ?\Closure $rowTransformer = null
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

    public function getItems(bool $flush = false): iterable
    {
        if (null === $this->results) {
            if (0 < $this->searchQuery->getPageSize()) {
                $this->queryBuilder->setFirstResult($this->searchQuery->getPage() * $this->searchQuery->getPageSize());
                $this->queryBuilder->setMaxResults($this->searchQuery->getPageSize());
            }

            $this->results = $this->queryBuilder
                ->getQuery()
                ->setHint(SqlWalker::HINT_DISTINCT, true)
                ->toIterable();
        }

        if (null !== $this->rowTransformer) {
            $count = 0;
            foreach ($this->results as $result) {
                yield ($this->rowTransformer)($result);

                ++$count;
                if (0 === $count % 30 && $flush) {
                    $this->queryBuilder->getEntityManager()->clear();
                    flush();
                }
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
            ->getQuery()
            ->setHint(SqlWalker::HINT_DISTINCT, true);
    }

    /**
     * @deprecated dev only
     */
    public function getQuery(): Query
    {
        return $this->queryBuilder->getQuery();
    }

    public function toResponse(): StreamedJsonResponse
    {
        return new StreamedJsonResponse([
            'totalResults' => $this->count(),
            'data' => $this->getItems(true),
        ]);
    }
}
