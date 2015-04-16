<?php

namespace HeVinci\CompetencyBundle\Adapter;

use Doctrine\ORM\Query;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * Adapter used as an alternative to the DoctrineORMAdapter, when
 * queries must return array results (the doctrine paginator used
 * in the default adapter can handle only entities).
 */
class OrmArrayAdapter implements AdapterInterface
{
    private $countQuery;
    private $resultQuery;
    private $cachedCount = null;

    /**
     * Constructor.
     *
     * @param Query $countQuery     The query for counting the whole result set
     * @param Query $resultQuery    The query for fetching the whole result set
     */
    public function __construct(Query $countQuery, Query $resultQuery)
    {
        $this->countQuery = $countQuery;
        $this->resultQuery = $resultQuery;
    }

    public function getNbResults()
    {
        if ($this->cachedCount === null) {
            $this->cachedCount = $this->countQuery->getSingleScalarResult();
        }

        return $this->cachedCount;
    }

    public function getSlice($offset, $length)
    {
        return $this->resultQuery->setMaxResults($length)
            ->setFirstResult($offset)
            ->getArrayResult();
    }
}
