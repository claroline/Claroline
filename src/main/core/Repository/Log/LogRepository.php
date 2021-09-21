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
use Claroline\CoreBundle\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
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

    public function findTopWorkspaceByAction(
        array $filters = [],
        $limit = -1
    ) {
        $qb = $this
            ->createQueryBuilder('obj')
            ->select('ws.id, ws.name, ws.code, count(obj.id) AS actions')
            ->leftJoin('obj.workspace', 'ws')
            ->groupBy('ws')
            ->orderBy('actions', 'DESC');

        $this->finder->configureQueryBuilder($qb, $filters);

        if ($limit > 0) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    public function findTopResourcesByAction(
        array $filters = [],
        $limit = -1
    ) {
        $qb = $this
            ->createQueryBuilder('obj')
            ->select('node.id, node.name, count(obj.id) AS actions')
            ->leftJoin('obj.resourceNode', 'node')
            ->groupBy('node.id')
            ->orderBy('actions', 'DESC');
        $this->finder->configureQueryBuilder($qb, $filters);

        if ($limit > 0) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    // TODO: Clean old methods after refactoring

    public function findFilteredLogsQuery(
        $action,
        $range,
        $userSearch,
        $actionsRestriction,
        $workspaceIds = null,
        $maxResult = -1,
        $resourceType = null,
        $resourceNodeIds = null,
        $groupSearch = null
    ) {
        $queryBuilder = $this
            ->createQueryBuilder('log')
            ->orderBy('log.dateLog', 'DESC');

        $queryBuilder = $this->addActionFilterToQueryBuilder($queryBuilder, $action, $actionsRestriction);
        $queryBuilder = $this->addDateRangeFilterToQueryBuilder($queryBuilder, $range);
        $queryBuilder = $this->addUserFilterToQueryBuilder($queryBuilder, $userSearch);
        $queryBuilder = $this->addResourceTypeFilterToQueryBuilder($queryBuilder, $resourceType);
        $queryBuilder = $this->addGroupFilterToQueryBuilder($queryBuilder, $groupSearch);

        if (null !== $workspaceIds && count($workspaceIds) > 0) {
            $queryBuilder = $this->addWorkspaceFilterToQueryBuilder($queryBuilder, $workspaceIds);
        }
        if (null !== $resourceNodeIds && count($resourceNodeIds) > 0) {
            $queryBuilder = $this->addResourceFilterToQueryBuilder($queryBuilder, $resourceNodeIds);
        }

        if ($maxResult > 0) {
            $queryBuilder->setMaxResults($maxResult);
        }

        return $queryBuilder->getQuery();
    }

    public function findFilteredLogs($action, $range, $userSearch, $actionsRestriction, $workspaceIds)
    {
        return $this->findFilteredLogsQuery(
            $action,
            $range,
            $userSearch,
            $actionsRestriction,
            $workspaceIds
        )->getResult();
    }

    //this method is never used and not up to date.
    public function findActionAfterDate(
        $action,
        $date,
        $doerId = null,
        $resourceId = null,
        $workspaceId = null,
        $receiverId = null,
        $roleId = null,
        $groupId = null,
        $toolName = null,
        $userType = null
    ) {
        $queryBuilder = $this
            ->createQueryBuilder('log')
            ->orderBy('log.dateLog', 'DESC')

            ->andWhere('log.action = :action')
            ->setParameter('action', $action)

            ->andWhere('log.dateLog >= :date')
            ->setParameter('date', $date);

        if (null !== $doerId) {
            $queryBuilder
                ->leftJoin('log.doer', 'doer')
                ->andWhere('doer.id = :doerId')
                ->setParameter('doerId', $doerId);
        }

        if (null !== $resourceId) {
            $queryBuilder
                ->leftJoin('log.resource', 'resource')
                ->andWhere('resource.id = :resourceId')
                ->setParameter('resourceId', $resourceId);
        }

        if (null !== $workspaceId) {
            $queryBuilder
                ->leftJoin('log.workspace', 'workspace')
                ->andWhere('workspace.id = :workspaceId')
                ->setParameter('workspaceId', $workspaceId);
        }

        if (null !== $receiverId) {
            $queryBuilder
                ->leftJoin('log.receiver', 'receiver')
                ->andWhere('receiver.id = :receiverId')
                ->setParameter('receiverId', $receiverId);
        }

        if (null !== $roleId) {
            $queryBuilder
                ->leftJoin('log.role', 'role')
                ->andWhere('role.id = :roleId')
                ->setParameter('roleId', $roleId);
        }

        if (null !== $groupId) {
            $queryBuilder
                ->leftJoin('log.receiverGroup', 'receiverGroup')
                ->andWhere('receiverGroup.id = :groupId')
                ->setParameter('groupId', $groupId);
        }

        if (null !== $toolName) {
            $queryBuilder
                ->andWhere('log.toolName = :toolName')
                ->setParameter('toolName', $toolName);
        }

        $q = $queryBuilder->getQuery();
        $logs = $q->getResult();

        return $logs;
    }

    public function findUserActionsByDay(
        $action,
        $range,
        $actionsRestriction,
        $workspaceIds,
        $resourceType,
        $resourceNodeIds,
        $userIds
    ) {
        $queryBuilder = $this
            ->createQueryBuilder('log')
            ->select(
                'doer.id, '
                .'log.shortDateLog as shortDate, '
                ."CONCAT(CONCAT(doer.id, '#'), log.shortDateLog) as criteria, "
                .'count(log.id) as total'
            )
            ->leftJoin('log.doer', 'doer')
            ->groupBy('criteria')
            ->addOrderBy('doer.id', 'ASC')
            ->addOrderBy('shortDate', 'ASC');

        $queryBuilder = $this->addActionFilterToQueryBuilder($queryBuilder, $action, $actionsRestriction);
        $queryBuilder = $this->addDateRangeFilterToQueryBuilder($queryBuilder, $range);
        $queryBuilder = $this->addResourceTypeFilterToQueryBuilder($queryBuilder, $resourceType);

        if (null !== $workspaceIds && count($workspaceIds) > 0) {
            $queryBuilder = $this->addWorkspaceFilterToQueryBuilder($queryBuilder, $workspaceIds);
        }
        if (null !== $resourceNodeIds && count($resourceNodeIds) > 0) {
            $queryBuilder = $this->addResourceFilterToQueryBuilder($queryBuilder, $resourceNodeIds);
        }
        if (null !== $userIds && count($userIds) > 0) {
            $queryBuilder = $this->addUserIdsFilterToQueryBuilder($queryBuilder, $userIds);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    public function addActionFilterToQueryBuilder(QueryBuilder $queryBuilder, $action, $actionRestriction = null)
    {
        if (null !== $actionRestriction) {
            if ('admin' === $actionRestriction) {
                $queryBuilder->andWhere('log.isDisplayedInAdmin = true');
            } elseif ('workspace' === $actionRestriction) {
                $queryBuilder->andWhere('log.isDisplayedInWorkspace = true');
            }
        }

        if (null !== $action && 'all' !== $action) {
            $queryBuilder
                ->andWhere('log.action LIKE :action')
                ->setParameter('action', '%'.$action.'%');
        }

        return $queryBuilder;
    }

    /**
     * @param array $range
     *
     * @return QueryBuilder
     */
    public function addDateRangeFilterToQueryBuilder(QueryBuilder $queryBuilder, $range)
    {
        if (null !== $range && 2 === count($range)) {
            $startDate = new \DateTime();
            $startDate->setTimestamp($range[0]);
            $startDate->setTime(0, 0, 0);

            $endDate = new \DateTime();
            $endDate->setTimestamp($range[1]);
            $endDate->setTime(23, 59, 59);

            $queryBuilder
                ->andWhere('log.dateLog >= :startDate')
                ->andWhere('log.dateLog <= :endDate')
                ->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate);
        }

        return $queryBuilder;
    }

    /**
     * @param string $userSearch
     *
     * @return QueryBuilder
     */
    private function addUserFilterToQueryBuilder(QueryBuilder $queryBuilder, $userSearch)
    {
        if (null !== $userSearch && '' !== $userSearch) {
            $upperUserSearch = strtoupper($userSearch);
            $upperUserSearch = trim($upperUserSearch);
            $upperUserSearch = preg_replace('/\s+/', ' ', $upperUserSearch);

            $queryBuilder->leftJoin('log.doer', 'doer');
            $queryBuilder->andWhere(
                $queryBuilder->expr()->orx(
                    $queryBuilder->expr()->like('UPPER(doer.lastName)', ':userSearch'),
                    $queryBuilder->expr()->like('UPPER(doer.firstName)', ':userSearch'),
                    $queryBuilder->expr()->like('UPPER(doer.username)', ':userSearch'),
                    $queryBuilder->expr()->like(
                        "CONCAT(CONCAT(UPPER(doer.firstName), ' '), UPPER(doer.lastName))",
                        ':userSearch'
                    ),
                    $queryBuilder->expr()->like(
                        "CONCAT(CONCAT(UPPER(doer.lastName), ' '), UPPER(doer.firstName))",
                        ':userSearch'
                    )
                )
            );

            $queryBuilder->setParameter('userSearch', '%'.$upperUserSearch.'%');
        }

        return $queryBuilder;
    }

    /**
     * @param string $userSearch
     *
     * @return QueryBuilder
     */
    private function addGroupFilterToQueryBuilder(QueryBuilder $queryBuilder, $groupSearch)
    {
        if (null !== $groupSearch && '' !== $groupSearch) {
            $upperUserSearch = strtoupper($groupSearch);
            $upperUserSearch = trim($groupSearch);
            $upperUserSearch = preg_replace('/\s+/', ' ', $upperUserSearch);

            $queryBuilder->leftJoin('log.doer', 'doer');
            $queryBuilder->leftJoin('doer.groups', 'groupDoer');
            $queryBuilder->andWhere('groupDoer.name LIKE :groupSearch');

            $queryBuilder->setParameter('groupSearch', '%'.$upperUserSearch.'%');
        }

        return $queryBuilder;
    }

    private function addWorkspaceFilterToQueryBuilder($queryBuilder, $workspaceIds)
    {
        if (null !== $workspaceIds && count($workspaceIds) > 0) {
            $queryBuilder->leftJoin('log.workspace', 'workspace');
            if (1 === count($workspaceIds)) {
                $queryBuilder->andWhere('workspace.id = :workspaceId');
                $queryBuilder->setParameter('workspaceId', $workspaceIds[0]);
            } else {
                $queryBuilder->andWhere('workspace.id IN (:workspaceIds)')->setParameter('workspaceIds', $workspaceIds);
            }
        }

        return $queryBuilder;
    }

    private function addUserIdsFilterToQueryBuilder($queryBuilder, $userIds)
    {
        if (null !== $userIds && count($userIds) > 0) {
            if (1 === count($userIds)) {
                $queryBuilder->andWhere('doer.id = :userId');
                $queryBuilder->setParameter('userId', $userIds[0]);
            } else {
                $queryBuilder->andWhere('doer.id IN (:userIds)')->setParameter('userIds', $userIds);
            }
        }

        return $queryBuilder;
    }

    private function addResourceFilterToQueryBuilder($queryBuilder, $resourceNodeIds)
    {
        if (null !== $resourceNodeIds && count($resourceNodeIds) > 0) {
            $queryBuilder->leftJoin('log.resourceNode', 'resource');
            if (1 === count($resourceNodeIds)) {
                $queryBuilder->andWhere('resource.id = :resourceId');
                $queryBuilder->setParameter('resourceId', $resourceNodeIds[0]);
            } else {
                $queryBuilder->andWhere('resource.id IN (:resourceNodeIds)')
                    ->setParameter('resourceNodeIds', $resourceNodeIds);
            }
        }

        return $queryBuilder;
    }

    private function addResourceTypeFilterToQueryBuilder($queryBuilder, $resourceType)
    {
        if (!empty($resourceType)) {
            $queryBuilder
                ->leftJoin('log.resourceType', 'resourceType')
                ->andWhere('resourceType.name = :resourceType')
                ->setParameter('resourceType', $resourceType);
        }

        return $queryBuilder;
    }

    public function extractChartData($result, $range)
    {
        $chartData = [];
        if (count($result) > 0) {
            //We send an array indexed by date dans contains count
            $lastDay = null;
            $endDay = null;
            if (null !== $range && 2 === count($range)) {
                $lastDay = new \DateTime();
                $lastDay->setTimestamp($range[0]);

                $endDay = new \DateTime();
                $endDay->setTimestamp($range[1]);
            }

            foreach ($result as $line) {
                if (null !== $lastDay) {
                    while ($lastDay->getTimestamp() < $line['shortDate']->getTimestamp()) {
                        $chartData[] = [$lastDay->getTimestamp() * 1000, 0];
                        $lastDay->add(new \DateInterval('P1D')); // P1D means a period of 1 day
                    }
                } else {
                    $lastDay = $line['shortDate'];
                }
                $lastDay->add(new \DateInterval('P1D')); // P1D means a period of 1 day

                $chartData[] = [$line['shortDate']->getTimestamp() * 1000, intval($line['total'])];
            }

            while ($lastDay->getTimestamp() <= $endDay->getTimestamp()) {
                $chartData[] = [$lastDay->getTimestamp() * 1000, 0];

                $lastDay->add(new \DateInterval('P1D')); // P1D means a period of 1 day
            }
        }

        return $chartData;
    }

    /**
     * @return QueryBuilder
     */
    public function addOwnerFilterToQueryBuilder(QueryBuilder $queryBuilder, User $owner)
    {
        $queryBuilder
            ->andWhere('log.owner = :owner')
            ->setParameter('owner', $owner);

        return $queryBuilder;
    }

    /**
     * @param int $otherElementId
     *
     * @return QueryBuilder
     */
    public function addOtherElementIdFilterToQueryBuilder(QueryBuilder $queryBuilder, $otherElementId)
    {
        $queryBuilder
            ->andWhere('log.otherElementId = :otherElementId')
            ->setParameter('otherElementId', $otherElementId);

        return $queryBuilder;
    }
}
