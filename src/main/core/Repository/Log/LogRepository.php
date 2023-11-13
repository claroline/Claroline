<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Repository\Log;

use Claroline\CoreBundle\API\Finder\Log\LogFinder;
use Claroline\CoreBundle\Entity\Log\Log;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LogRepository extends ServiceEntityRepository
{
    /** @var LogFinder */
    private $finder;

    public function __construct(ManagerRegistry $registry, LogFinder $finder)
    {
        $this->finder = $finder;

        parent::__construct($registry, Log::class);
    }

    /**
     * Fetches data for line chart.
     *
     * @param bool $unique
     *
     * @return array
     */
    public function fetchChartData(array $filters = [], $unique = false)
    {
        $qb = $this->createQueryBuilder('obj');

        if (true === $unique) {
            $qb->select('obj.shortDateLog as date, COUNT(DISTINCT obj.doer) as total');
        } else {
            $qb->select('obj.shortDateLog as date, COUNT(obj.id) as total');
        }
        $qb
            ->orderBy('date', 'ASC')
            ->groupBy('date');

        $this->finder->configureQueryBuilder($qb, $filters);

        return $qb->getQuery()->getResult();
    }

    public function fetchUserActionsList(
        array $filters = [],
        $count = false,
        $page = 0,
        $limit = -1,
        $sortBy = null
    ) {
        $qb = $this->createQueryBuilder('obj');
        $this->finder->configureQueryBuilder($qb, $filters, $limit < 0 ? $sortBy : []);
        if ($count) {
            $qb->select('COUNT(DISTINCT obj.doer)');
        } else {
            $qb->select('
                doer.id as doerId,
                doer.firstName as doerFirstName,
                doer.lastName as doerLastName,
                doer.picture as doerPicture,
                obj.shortDateLog as date,
                CONCAT(CONCAT(IDENTITY(obj.doer), \'#\'), obj.shortDateLog) as criteria,
                COUNT(obj.id) as total
            ')
                ->groupBy('criteria');
            if (!in_array('doer', $qb->getAllAliases())) {
                $qb->join('obj.doer', 'doer');
            }
            if ($limit > 0) {
                $ids = array_column($this->fetchUsersByActionsList($filters, true, $page, $limit, $sortBy), 'doerId');
                $qb->andWhere('obj.doer IN (:ids)')
                    ->setParameter('ids', $ids);
            }
            if (empty($sortBy) || empty($sortBy['property']) || 'doer.name' !== $sortBy['property']) {
                $qb->addOrderBy('obj.doer');
            }
            $qb->addOrderBy('date');
        }

        return $count ? $qb->getQuery()->getSingleScalarResult() : $qb->getQuery()->getResult();
    }

    public function fetchUsersByActionsList(
        array $filters = [],
        $idsOnly = false,
        $page = 0,
        $limit = -1,
        $sortBy = null
    ) {
        $qb = $this->createQueryBuilder('obj');
        if ($idsOnly) {
            $qb->select('DISTINCT(IDENTITY(obj.doer)) AS doerId, COUNT(obj.id) AS actions');
        } else {
            $qb->select('
                DISTINCT(doer.id) AS doerId,
                doer.firstName AS doerFirstName,
                doer.lastName AS doerLastName,
                COUNT(obj.id) AS actions
            ');
        }

        if ($limit > 0) {
            $qb->setFirstResult($page * $limit);
            $qb->setMaxResults($limit);
        }

        $this->finder->configureQueryBuilder($qb, $filters, $sortBy);

        if (!$idsOnly && !in_array('doer', $qb->getAllAliases())) {
            $qb->join('obj.doer', 'doer');
        }

        $qb->groupBy('obj.doer');

        return $qb->getQuery()->getResult();
    }
}
