<?php

namespace Claroline\CoreBundle\Repository\Log;

use Claroline\CoreBundle\Event\Log\LogUserLoginEvent;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;

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
        if ($configs === null || count($configs) == 0) {
            return null;
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

    public function countByDayFilteredLogs($action, $range, $userSearch, $actionRestriction, $workspaceIds = null, $unique = false)
    {
        $queryBuilder = $this
            ->createQueryBuilder('log')
            ->orderBy('shortDate', 'ASC')
            ->groupBy('shortDate');

        if ($unique === true) {
            $queryBuilder->select('log.shortDateLog as shortDate, count(DISTINCT log.doer) as total');
        }
        else {
            $queryBuilder->select('log.shortDateLog as shortDate, count(log.id) as total');
        }

        $queryBuilder = $this->addActionFilterToQueryBuilder($queryBuilder, $action, $actionRestriction);
        $queryBuilder = $this->addDateRangeFilterToQueryBuilder($queryBuilder, $range);
        $queryBuilder = $this->addUserFilterToQueryBuilder($queryBuilder, $userSearch);

        if ($workspaceIds !== null and count($workspaceIds) > 0) {
            $queryBuilder = $this->addWorkspaceFilterToQueryBuilder($queryBuilder, $workspaceIds);
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
        if ($configs === null || count($configs) == 0) {
            return null;
        }

        $queryBuilder = $this
            ->createQueryBuilder('log')
            ->leftJoin('log.workspace', 'workspace')
            ->orderBy('log.dateLog', 'ASC');

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
        $maxResult = -1
    )
    {
        $queryBuilder = $this
            ->createQueryBuilder('log')
            ->orderBy('log.dateLog', 'DESC')
        ;

        $queryBuilder = $this->addActionFilterToQueryBuilder($queryBuilder, $action, $actionsRestriction);
        $queryBuilder = $this->addDateRangeFilterToQueryBuilder($queryBuilder, $range);
        $queryBuilder = $this->addUserFilterToQueryBuilder($queryBuilder, $userSearch);

        if ($workspaceIds !== null and count($workspaceIds) > 0) {
            $queryBuilder = $this->addWorkspaceFilterToQueryBuilder($queryBuilder, $workspaceIds);
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
    )
    {
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

    public function topWSByAction ($range, $action, $max)
    {
        $queryBuilder = $this
            ->createQueryBuilder('log')
            ->select('ws.id, ws.name, ws.code, count(log.id) AS actions')
            ->leftJoin('log.workspace', 'ws')
            ->groupBy('ws')
            ->orderBy('actions', 'DESC');

        if ($max > 1) {
            $queryBuilder->setMaxResults($max);
        }

        $queryBuilder = $this->addActionFilterToQueryBuilder($queryBuilder, $action, null);
        $queryBuilder = $this->addDateRangeFilterToQueryBuilder($queryBuilder, $range);
        $query = $queryBuilder->getQuery();

        return $query->getResult();
    }

    public function topMediaByAction ($range, $action, $max)
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

        if ($max > 1) {
            $queryBuilder->setMaxResults($max);
        }

        $queryBuilder = $this->addActionFilterToQueryBuilder($queryBuilder, $action, null);
        $queryBuilder = $this->addDateRangeFilterToQueryBuilder($queryBuilder, $range);
        $query = $queryBuilder->getQuery();

        return $query->getResult();
    }

    public function topResourcesByAction ($range, $action, $max)
    {
        $queryBuilder = $this
            ->createQueryBuilder('log')
            ->select('node.id, node.name, count(log.id) AS actions')
            ->leftJoin('log.resourceNode', 'node')
            ->groupBy('node')
            ->orderBy('actions', 'DESC');

        if ($max > 1) {
            $queryBuilder->setMaxResults($max);
        }

        $queryBuilder = $this->addActionFilterToQueryBuilder($queryBuilder, $action, null);
        $queryBuilder = $this->addDateRangeFilterToQueryBuilder($queryBuilder, $range);
        $query = $queryBuilder->getQuery();

        return $query->getResult();
    }

    public function topUsersByAction($range, $action, $max)
    {
        $queryBuilder = $this
            ->createQueryBuilder('log')
            ->select(
                'doer.id, '
                . "CONCAT(CONCAT(doer.firstName, ' '), doer.lastName) AS name, "
                . 'doer.username, count(log.id) AS actions'
            )
            ->leftJoin('log.doer', 'doer')
            ->groupBy('doer')
            ->orderBy('actions', 'DESC')
        ;

        if ($max > 1) {
            $queryBuilder->setMaxResults($max);
        }

        $queryBuilder = $this->addActionFilterToQueryBuilder($queryBuilder, $action);
        $queryBuilder = $this->addDateRangeFilterToQueryBuilder($queryBuilder, $range);
        $query        = $queryBuilder->getQuery();

        return $query->getResult();
    }

    public function activeUsers ()
    {
        $queryBuilder = $this
            ->createQueryBuilder('log')
            ->select('COUNT(DISTINCT log.doer) AS users');

        $queryBuilder = $this->addActionFilterToQueryBuilder($queryBuilder, LogUserLoginEvent::ACTION);

        $query = $queryBuilder->getQuery();
        $result = $query->getResult();

        return $result[0]['users'];
    }

    private function addActionFilterToQueryBuilder(QueryBuilder $queryBuilder, $action, $actionRestriction = null)
    {
        if (null !== $actionRestriction) {
            if ('admin' === $actionRestriction) {
                $queryBuilder->andWhere('log.isDisplayedInAdmin = true');
            }
            elseif('workspace' === $actionRestriction) {
                $queryBuilder->andWhere('log.isDisplayedInWorkspace = true');
            }
        }

        if (null !== $action) {
            $queryBuilder
                ->andWhere("log.action LIKE :action")
                ->setParameter('action', '%' . $action . '%');
        }

        return $queryBuilder;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param array        $range
     *
     * @return QueryBuilder
     */
    private function addDateRangeFilterToQueryBuilder(QueryBuilder $queryBuilder, $range)
    {
        if ($range !== null and count($range) == 2) {
            $startDate = new \DateTime();
            $startDate->setTimestamp($range[0]);
            $startDate->setTime(0, 0, 0);

            $endDate = new \DateTime();
            $endDate->setTimestamp($range[1]);
            $endDate->setTime(23, 59, 59);

            $queryBuilder
                ->andWhere("log.dateLog >= :startDate")
                ->andWhere("log.dateLog <= :endDate")
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

            $queryBuilder->setParameter('userSearch', '%' . $upperUserSearch . '%');
        }

        return $queryBuilder;
    }

    private function addWorkspaceFilterToQueryBuilder($queryBuilder, $workspaceIds)
    {
        if ($workspaceIds !== null and count($workspaceIds) > 0) {
            $queryBuilder->leftJoin('log.workspace', 'workspace');
            if (count($workspaceIds) == 1) {
                $queryBuilder->andWhere("workspace.id = :workspaceId");
                $queryBuilder->setParameter('workspaceId', $workspaceIds[0]);
            } else {
                $queryBuilder->andWhere("workspace.id IN (:workspaceIds)")->setParameter('workspaceIds', $workspaceIds);
            }
        }

        return $queryBuilder;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param \Claroline\CoreBundle\Entity\Log\LogWorkspaceWidgetConfig[] $configs
     *
     * @return mixed
     */
    private function addConfigurationFilterToQueryBuilder(QueryBuilder $queryBuilder, $configs)
    {
        $actionIndex = 0;
        foreach ($configs as $config) {
            $workspaceId = $config->getWorkspace()->getId();
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

    private function extractChartData($result, $range)
    {
        $chartData = array();
        if (count($result) > 0) {
            //We send an array indexed by date dans contains count
            $lastDay = null;
            $endDay = null;
            if ($range !== null and count($range) == 2) {
                $lastDay = new \DateTime();
                $lastDay->setTimestamp($range[0]);

                $endDay = new \DateTime();
                $endDay->setTimestamp($range[1]);
            }

            foreach ($result as $line) {
                if ($lastDay !== null) {
                    while ($lastDay->getTimestamp() < $line['shortDate']->getTimestamp()) {
                        $chartData[] = array($lastDay->getTimestamp() * 1000, 0);
                        $lastDay->add(new \DateInterval('P1D')); // P1D means a period of 1 day
                    }
                } else {
                    $lastDay = $line['shortDate'];
                }
                $lastDay->add(new \DateInterval('P1D')); // P1D means a period of 1 day

                $chartData[] = array($line['shortDate']->getTimestamp() * 1000, intval($line['total']));
            }

            while ($lastDay->getTimestamp() <= $endDay->getTimestamp()) {
                $chartData[] = array($lastDay->getTimestamp() * 1000, 0);

                $lastDay->add(new \DateInterval('P1D')); // P1D means a period of 1 day
            }
        }

        return $chartData;
    }
}
