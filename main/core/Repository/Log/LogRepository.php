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

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Log\LogUserLoginEvent;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class LogRepository extends EntityRepository
{
    /**
     * @param $configs
     * @param $range
     *
     * @return array|null
     */
    public function countByDayThroughConfigs($configs, $range)
    {
        if ($configs === null || count($configs) === 0) {
            return;
        }

        $queryBuilder = $this
            ->createQueryBuilder('log')
            ->leftJoin('log.workspace', 'workspace')
            ->select('log.shortDateLog as shortDate, count(log.id) as total')
            ->orderBy('shortDate', 'ASC')
            ->groupBy('shortDate');

        $queryBuilder = $this->addConfigurationFilterToQueryBuilder($queryBuilder, $configs);

        return $this->extractChartData($queryBuilder->getQuery()->getResult(), $range);
    }

    public function countByDayFilteredLogs(
        $action,
        $range,
        $userSearch,
        $actionRestriction,
        $workspaceIds = null,
        $unique = false,
        $resourceType = null,
        $resourceNodeIds = null,
        $groupSearch = null
    ) {
        $queryBuilder = $this
            ->createQueryBuilder('log')
            ->orderBy('shortDate', 'ASC')
            ->groupBy('shortDate');

        if ($unique === true) {
            $queryBuilder->select('log.shortDateLog as shortDate, count(DISTINCT log.doer) as total');
        } else {
            $queryBuilder->select('log.shortDateLog as shortDate, count(log.id) as total');
        }

        $queryBuilder = $this->addActionFilterToQueryBuilder($queryBuilder, $action, $actionRestriction);
        $queryBuilder = $this->addDateRangeFilterToQueryBuilder($queryBuilder, $range);
        $queryBuilder = $this->addUserFilterToQueryBuilder($queryBuilder, $userSearch);
        $queryBuilder = $this->addResourceTypeFilterToQueryBuilder($queryBuilder, $resourceType);
        $queryBuilder = $this->addGroupFilterToQueryBuilder($queryBuilder, $groupSearch);

        if ($workspaceIds !== null && count($workspaceIds) > 0) {
            $queryBuilder = $this->addWorkspaceFilterToQueryBuilder($queryBuilder, $workspaceIds);
        }
        if ($resourceNodeIds !== null && count($resourceNodeIds) > 0) {
            $queryBuilder = $this->addResourceFilterToQueryBuilder($queryBuilder, $resourceNodeIds);
        }

        return $this->extractChartData($queryBuilder->getQuery()->getResult(), $range);
    }

    /**
     * @param $configs
     * @param $maxResult
     *
     * @return null|Query
     */
    public function findLogsThroughConfigs($configs, $maxResult = -1)
    {
        if ($configs === null || count($configs) === 0) {
            return;
        }

        $queryBuilder = $this
            ->createQueryBuilder('log')
            ->leftJoin('log.workspace', 'workspace')
            ->orderBy('log.dateLog', 'DESC');

        $queryBuilder = $this->addConfigurationFilterToQueryBuilder($queryBuilder, $configs);

        if ($maxResult > 0) {
            $queryBuilder->setMaxResults($maxResult);
        }

        return $queryBuilder->getQuery();
    }

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

        if ($workspaceIds !== null && count($workspaceIds) > 0) {
            $queryBuilder = $this->addWorkspaceFilterToQueryBuilder($queryBuilder, $workspaceIds);
        }
        if ($resourceNodeIds !== null && count($resourceNodeIds) > 0) {
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

        if ($doerId !== null) {
            $queryBuilder
                ->leftJoin('log.doer', 'doer')
                ->andWhere('doer.id = :doerId')
                ->setParameter('doerId', $doerId);
        }

        if ($resourceId !== null) {
            $queryBuilder
                ->leftJoin('log.resource', 'resource')
                ->andWhere('resource.id = :resourceId')
                ->setParameter('resourceId', $resourceId);
        }

        if ($workspaceId !== null) {
            $queryBuilder
                ->leftJoin('log.workspace', 'workspace')
                ->andWhere('workspace.id = :workspaceId')
                ->setParameter('workspaceId', $workspaceId);
        }

        if ($receiverId !== null) {
            $queryBuilder
                ->leftJoin('log.receiver', 'receiver')
                ->andWhere('receiver.id = :receiverId')
                ->setParameter('receiverId', $receiverId);
        }

        if ($roleId !== null) {
            $queryBuilder
                ->leftJoin('log.role', 'role')
                ->andWhere('role.id = :roleId')
                ->setParameter('roleId', $roleId);
        }

        if ($groupId !== null) {
            $queryBuilder
                ->leftJoin('log.receiverGroup', 'receiverGroup')
                ->andWhere('receiverGroup.id = :groupId')
                ->setParameter('groupId', $groupId);
        }

        if ($toolName !== null) {
            $queryBuilder
                ->andWhere('log.toolName = :toolName')
                ->setParameter('toolName', $toolName);
        }

        $q = $queryBuilder->getQuery();
        $logs = $q->getResult();

        return $logs;
    }

    public function topWSByAction($range, $action, $max)
    {
        $queryBuilder = $this
            ->createQueryBuilder('log')
            ->select('ws.id, ws.name, ws.code, count(log.id) AS actions')
            ->leftJoin('log.workspace', 'ws')
            ->groupBy('ws')
            ->orderBy('actions', 'DESC');

        if ($max > 0) {
            $queryBuilder->setMaxResults($max);
        }

        $queryBuilder = $this->addActionFilterToQueryBuilder($queryBuilder, $action, null);
        $queryBuilder = $this->addDateRangeFilterToQueryBuilder($queryBuilder, $range);
        $query = $queryBuilder->getQuery();

        return $query->getResult();
    }

    public function topMediaByAction($range, $action, $max)
    {
        $queryBuilder = $this
            ->createQueryBuilder('log')
            ->select('node.id, node.name, count(log.id) AS actions')
            ->leftJoin('log.resourceNode', 'node')
            ->leftJoin('log.resourceType', 'resource_type')
            ->andWhere('resource_type.name=:fileType')
            ->groupBy('node')
            ->orderBy('actions', 'DESC')
            ->setParameter('fileType', 'file');

        if ($max > 0) {
            $queryBuilder->setMaxResults($max);
        }

        $queryBuilder = $this->addActionFilterToQueryBuilder($queryBuilder, $action, null);
        $queryBuilder = $this->addDateRangeFilterToQueryBuilder($queryBuilder, $range);
        $query = $queryBuilder->getQuery();

        return $query->getResult();
    }

    public function topResourcesByAction($range, $action, $max)
    {
        $queryBuilder = $this
            ->createQueryBuilder('log')
            ->select('node.id, node.name, count(log.id) AS actions')
            ->leftJoin('log.resourceNode', 'node')
            ->groupBy('node')
            ->orderBy('actions', 'DESC');

        if ($max > 0) {
            $queryBuilder->setMaxResults($max);
        }

        $queryBuilder = $this->addActionFilterToQueryBuilder($queryBuilder, $action, null);
        $queryBuilder = $this->addDateRangeFilterToQueryBuilder($queryBuilder, $range);
        $query = $queryBuilder->getQuery();

        return $query->getResult();
    }

    public function topUsersByAction($range, $action, $max)
    {
        $query = $this->topUsersByActionQuery($action, $range, null, null, null, $max);

        return $query->getResult();
    }

    public function topUsersByActionQuery(
        $action,
        $range,
        $userSearch,
        $actionsRestriction,
        $workspaceIds = null,
        $maxResult = -1,
        $resourceType = null,
        $resourceNodeIds = null,
        $enableAnonymous = true,
        $page = null,
        $orderBy = null,
        $order = 'DESC'
    ) {
        $queryBuilder = $this
            ->createQueryBuilder('log')
            ->select(
                'doer.id, '
                ."CONCAT(CONCAT(doer.firstName, ' '), doer.lastName) AS name, "
                ."CONCAT(CONCAT(doer.lastName, ' '), doer.firstName) AS sortingName, "
                .'doer.username, count(log.id) AS actions'
            )
            ->groupBy('doer');
        if ($orderBy === 'name') {
            $orderBy = 'sortingName';
        }
        if ($orderBy === null) {
            $orderBy = 'actions';
        }
        $queryBuilder->orderBy($orderBy, $order);
        $queryBuilder = $this->addActionFilterToQueryBuilder($queryBuilder, $action, $actionsRestriction);
        $queryBuilder = $this->addDateRangeFilterToQueryBuilder($queryBuilder, $range);
        if ($userSearch !== null && $userSearch !== '') {
            $queryBuilder = $this->addUserFilterToQueryBuilder($queryBuilder, $userSearch);
        } else {
            $queryBuilder->leftJoin('log.doer', 'doer');
        }
        $queryBuilder = $this->addResourceTypeFilterToQueryBuilder($queryBuilder, $resourceType);

        if ($workspaceIds !== null && count($workspaceIds) > 0) {
            $queryBuilder = $this->addWorkspaceFilterToQueryBuilder($queryBuilder, $workspaceIds);
        }
        if ($resourceNodeIds !== null && count($resourceNodeIds) > 0) {
            $queryBuilder = $this->addResourceFilterToQueryBuilder($queryBuilder, $resourceNodeIds);
        }
        if (!$enableAnonymous) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->isNotNull('log.doer')
            );
        }

        if ($maxResult > 0) {
            $queryBuilder->setMaxResults($maxResult);
            if ($page !== null) {
                $page = max(0, $page - 1);
                $queryBuilder->setFirstResult($page * $maxResult);
            }
        }

        return $queryBuilder->getQuery();
    }

    public function countTopUsersByAction(
        $action,
        $range,
        $userSearch,
        $actionsRestriction,
        $workspaceIds = null,
        $resourceType = null,
        $resourceNodeIds = null,
        $enableAnonymous = true
    ) {
        $queryBuilder = $this->createQueryBuilder('log');
        $queryBuilder->select($queryBuilder->expr()->countDistinct('log.doer'));
        $queryBuilder = $this->addActionFilterToQueryBuilder($queryBuilder, $action, $actionsRestriction);
        $queryBuilder = $this->addDateRangeFilterToQueryBuilder($queryBuilder, $range);
        $queryBuilder = $this->addUserFilterToQueryBuilder($queryBuilder, $userSearch);
        $queryBuilder = $this->addResourceTypeFilterToQueryBuilder($queryBuilder, $resourceType);

        if ($workspaceIds !== null && count($workspaceIds) > 0) {
            $queryBuilder = $this->addWorkspaceFilterToQueryBuilder($queryBuilder, $workspaceIds);
        }
        if ($resourceNodeIds !== null && count($resourceNodeIds) > 0) {
            $queryBuilder = $this->addResourceFilterToQueryBuilder($queryBuilder, $resourceNodeIds);
        }
        if (!$enableAnonymous) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->isNotNull('log.doer')
            );
        }
        $result = $queryBuilder->getQuery()->getSingleScalarResult();

        return intval($result);
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

        if ($workspaceIds !== null && count($workspaceIds) > 0) {
            $queryBuilder = $this->addWorkspaceFilterToQueryBuilder($queryBuilder, $workspaceIds);
        }
        if ($resourceNodeIds !== null && count($resourceNodeIds) > 0) {
            $queryBuilder = $this->addResourceFilterToQueryBuilder($queryBuilder, $resourceNodeIds);
        }
        if ($userIds !== null && count($userIds) > 0) {
            $queryBuilder = $this->addUserIdsFilterToQueryBuilder($queryBuilder, $userIds);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    public function activeUsers()
    {
        $queryBuilder = $this
            ->createQueryBuilder('log')
            ->select('COUNT(DISTINCT log.doer) AS users');

        $queryBuilder = $this->addActionFilterToQueryBuilder($queryBuilder, LogUserLoginEvent::ACTION);

        $query = $queryBuilder->getQuery();
        $result = $query->getResult();

        return $result[0]['users'];
    }

    public function activeUsersByDateRange($range)
    {
        $queryBuilder = $this
            ->createQueryBuilder('log')
            ->select('COUNT(DISTINCT log.doer) AS users');

        $queryBuilder = $this->addActionFilterToQueryBuilder($queryBuilder, LogUserLoginEvent::ACTION);

        $queryBuilder = $this->addDateRangeFilterToQueryBuilder($queryBuilder, $range);

        $query = $queryBuilder->getQuery();
        $result = $query->getResult();

        return $result[0]['users'];
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

        if (null !== $action && $action !== 'all') {
            $queryBuilder
                ->andWhere('log.action LIKE :action')
                ->setParameter('action', '%'.$action.'%');
        }

        return $queryBuilder;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param array        $range
     *
     * @return QueryBuilder
     */
    public function addDateRangeFilterToQueryBuilder(QueryBuilder $queryBuilder, $range)
    {
        if ($range !== null && count($range) === 2) {
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
     * @param QueryBuilder $queryBuilder
     * @param string       $userSearch
     *
     * @return QueryBuilder
     */
    private function addUserFilterToQueryBuilder(QueryBuilder $queryBuilder, $userSearch)
    {
        if ($userSearch !== null && $userSearch !== '') {
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
     * @param QueryBuilder $queryBuilder
     * @param string       $userSearch
     *
     * @return QueryBuilder
     */
    private function addGroupFilterToQueryBuilder(QueryBuilder $queryBuilder, $groupSearch)
    {
        if ($groupSearch !== null && $groupSearch !== '') {
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
        if ($workspaceIds !== null && count($workspaceIds) > 0) {
            $queryBuilder->leftJoin('log.workspace', 'workspace');
            if (count($workspaceIds) === 1) {
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
        if ($userIds !== null && count($userIds) > 0) {
            if (count($userIds) === 1) {
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
        if ($resourceNodeIds !== null && count($resourceNodeIds) > 0) {
            $queryBuilder->leftJoin('log.resourceNode', 'resource');
            if (count($resourceNodeIds) === 1) {
                $queryBuilder->andWhere('resource.id = :resourceId');
                $queryBuilder->setParameter('resourceId', $resourceNodeIds[0]);
            } else {
                $queryBuilder->andWhere('resource.id IN (:resourceNodeIds)')
                    ->setParameter('resourceNodeIds', $resourceNodeIds);
            }
        }

        return $queryBuilder;
    }

    /**
     * @param QueryBuilder                                         $queryBuilder
     * @param \Claroline\CoreBundle\Entity\Widget\WidgetInstance[] $configs
     *
     * @return mixed
     */
    private function addConfigurationFilterToQueryBuilder(QueryBuilder $queryBuilder, $configs)
    {
        foreach ($configs as $config) {
            $workspaceId = $config->getWidgetInstance()->getWorkspace()->getId();
            $queryBuilder
                ->where('workspace.id = :workspaceId')
                ->setParameter('workspaceId', $workspaceId);

            if ($config->hasRestriction()) {
                $queryBuilder
                    ->andWhere('log.action IN (:actions)')
                    ->setParameter('actions', $config->getRestrictions());
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
            if ($range !== null && count($range) === 2) {
                $lastDay = new \DateTime();
                $lastDay->setTimestamp($range[0]);

                $endDay = new \DateTime();
                $endDay->setTimestamp($range[1]);
            }

            foreach ($result as $line) {
                if ($lastDay !== null) {
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
     * @param QueryBuilder $queryBuilder
     * @param User         $owner
     *
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
     * @param QueryBuilder $queryBuilder
     * @param int          $otherElementId
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
