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
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use JMS\DiExtraBundle\Annotation as DI;

abstract class AbstractFinder implements FinderInterface
{
    use FinderTrait;

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

        if ($query instanceof QueryBuilder) {
            $qb = $query;
        }

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

    public function findOneBy(array $filters = [])
    {
        $data = $this->find($filters);

        if (count($data) > 1) {
            throw new \Exception('Multiple results found ('.count($data).')');
        } elseif (0 === count($data)) {
            return null;
        }

        return $data[0];
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

    //bad way to do it but otherwise we use a prepared statement and the sql contains '?'
    //https://stackoverflow.com/questions/2095394/doctrine-how-to-print-out-the-real-sql-not-just-the-prepared-statement/28294482
    protected function getSql(Query $query)
    {
        $vals = $query->getParameters();

        foreach (explode('?', $query->getSql()) as $i => $part) {
            $sql = (isset($sql) ? $sql : null).$part;
            if (isset($vals[$i])) {
                $value = $vals[$i]->getValue();
                //oh god... maybe more will required to be added here
                if (is_string($value)) {
                    $sql .= "'{$value}'";
                } elseif (is_array($value)) {
                    $value = array_map(function ($val) {
                        return is_string($val) ? "'$val'" : $val;
                    }, $value);
                    $sql .= implode(',', $value);
                } elseif (is_bool($value)) {
                    $sql .= $value ? 'TRUE' : 'FALSE';
                } else {
                    $sql .= $value;
                }
            }
        }

        return $sql;
    }
}
