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
        private readonly FinderRequest $request,
        private readonly QueryBuilder $queryBuilder,
        private readonly ?\Closure $rowTransformer = null,
        private readonly bool $readonly = true
    ) {
    }

    /**
     * Checks if the retrieved entities are marked as readOnly in the ORM.
     */
    public function isReadonly(): bool
    {
        return $this->readonly;
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
            $this->results = $this->getQuery()
                ->toIterable();
        }

        if (null !== $this->rowTransformer) {
            $count = 0;
            foreach ($this->results as $result) {
                $this->queryBuilder->getEntityManager()->getUnitOfWork()->markReadOnly($result);

                yield ($this->rowTransformer)($result);

                ++$count;
                if (0 === $count % 30 && $flush) {
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

    private function getQuery(): Query
    {
        if (0 < $this->request->getPageSize()) {
            $this->queryBuilder->setFirstResult($this->request->getPage() * $this->request->getPageSize());
            $this->queryBuilder->setMaxResults($this->request->getPageSize());
        }

        return $this->queryBuilder
            ->getQuery()
            ->setHint(SqlWalker::HINT_DISTINCT, true)
            ->setHint(Query::HINT_READ_ONLY, $this->readonly);
    }

    public function toResponse(): StreamedJsonResponse
    {
        return new StreamedJsonResponse([
            'totalResults' => $this->count(),
            'data' => $this->getItems(true),
        ]);
    }
}
