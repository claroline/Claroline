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
use Doctrine\ORM\NativeQuery;
use Doctrine\ORM\Query\Query;
use Doctrine\ORM\QueryBuilder;
use JMS\DiExtraBundle\Annotation as DI;

abstract class AbstractFinder implements FinderInterface
{
    protected $om;

    /**
     * @DI\InjectParams({
     *      "om" = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param ObjectManager $om
     */
    public function setObjectManager(ObjectManager $om)
    {
        $this->om = $om;
    }

    public function find(array $filters = [], array $sortBy = null, $page = 0, $limit = -1, $count = false)
    {
        //sorting is not required when we count stuff
        $sortBy = $count ? null : $sortBy;

        /** @var QueryBuilder $qb */
        $qb = $this->om->createQueryBuilder();
        $qb->select($count ? 'COUNT(DISTINCT obj)' : 'DISTINCT obj')->from($this->getClass(), 'obj');
        //make an option parameters for query builder ?
        $options = [
          'page' => $page,
          'limit' => $limit,
          'count' => $count,
        ];

        // filter query - let's the finder implementation process the filters to configure query
        $query = $this->configureQueryBuilder($qb, $filters, $sortBy, $options);

        if (!($query instanceof NativeQuery)) {
            // order query if implementation has not done it
            $this->sortResults($qb, $sortBy);
            if (!$count && 0 < $limit) {
                $qb->setFirstResult($page * $limit);
                $qb->setMaxResults($limit);
            }
            $query = $qb->getQuery();
        }

        return $count ? (int) $query->getSingleScalarResult() : $query->getResult();
    }

    /**
     * @param QueryBuilder $qb
     * @param array|null   $sortBy
     */
    private function sortResults(QueryBuilder $qb, array $sortBy = null)
    {
        if (!empty($sortBy) && !empty($sortBy['property']) && 0 !== $sortBy['direction']) {
            // query needs to be sorted, check if the Finder implementation has a custom sort system
            $queryOrder = $qb->getDQLPart('orderBy');
            if (empty($queryOrder)) {
                // no order by defined
                $qb->orderBy('obj.'.$sortBy['property'], 1 === $sortBy['direction'] ? 'ASC' : 'DESC');
            }
        }
    }
}
