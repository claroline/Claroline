<?php

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

class LogRepository extends EntityRepository
{

    private function actionsRestrictionToString($actionsRestriction)
    {
        $s = "";
        $first = true;
        foreach ($actionsRestriction as $action) {
            if ($first) {
                $first = false;
                $s .= "'".$action."'";
            } else {
                $s .= ", '".$action."'";
            }
        }

        return $s;
    }

    private function addActionFilterToQueryBuilder($qb, $action, $actionsRestriction)
    {
        if ($action == 'resource_all') {
            $action = array('resource_create',
                'resource_move',
                'resource_read',
                'resource_export',
                'resource_delete',
                'resource_update',
                'resource_shortcut',
                'resource_child_update');
        } elseif ($action == 'ws_role_all') {
            $action = array('ws_role_create',
                'ws_role_delete',
                'ws_role_update',
                'ws_role_change_right',
                'ws_role_subscribe_user',
                'ws_role_unsubscribe_user',
                'ws_role_subscribe_group',
                'ws_role_unsubscribe_group');
        } elseif ($action == 'group_all') {
            $action = array('group_add_user', 'group_create', 'group_delete', 'group_remove_user', 'group_update');
        } elseif ($action == 'user_all') {
            $action = array('user_create', 'user_delete', 'user_login', 'user_update');
        } elseif ($action == 'workspace_all') {
            $action = array('workspace_create', 'workspace_delete', 'workspace_update');
        } elseif ($action == 'all' or $action === null) {
            $action = $actionsRestriction;
        } else {
            $action = array($action);
        }

        if ($action !== null) {
            $qb->andWhere("log.action IN (:action)");
            $qb->setParameter('action', $action);
        }

        return $qb;
    }

    private function addDateRangeFilterToQueryBuilder($qb, $range)
    {
        if ($range !== null and count($range) == 2) {
            $startDate = new \DateTime();
            $startDate->setTimestamp($range[0]);
            $startDate->setTime(0, 0, 0);

            $endDate = new \DateTime();
            $endDate->setTimestamp($range[1]);
            $endDate->setTime(23, 59, 59);

            $qb
                ->andWhere("log.dateLog >= :startDate")
                ->andWhere("log.dateLog <= :endDate")
                ->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate);
        }

        return $qb;
    }

    private function addUserFilterToQueryBuilder($qb, $userSearch)
    {
        if ($userSearch !== null && $userSearch != '') {
            $upperUserSearch = strtoupper($userSearch);
            $upperUserSearch = trim($upperUserSearch);
            $upperUserSearch = preg_replace('/\s+/', ' ', $upperUserSearch);

            $qb->leftJoin('log.doer', 'doer');
            $qb->andWhere(
                $qb->expr()->orx(
                    $qb->expr()->like('UPPER(doer.lastName)', ':userSearch'),
                    $qb->expr()->like('UPPER(doer.firstName)', ':userSearch'),
                    $qb->expr()->like('UPPER(doer.username)', ':userSearch'),
                    $qb->expr()->like("CONCAT(CONCAT(UPPER(doer.firstName), ' '), UPPER(doer.lastName))", ':userSearch'),
                    $qb->expr()->like("CONCAT(CONCAT(UPPER(doer.lastName), ' '), UPPER(doer.firstName))", ':userSearch')
                )
            );

            $qb->setParameter('userSearch', '%'.$upperUserSearch.'%');
        }

        return $qb;
    }

    private function addWorkspaceFilterToQueryBuilder($qb, $workspaceIds)
    {
        if ($workspaceIds !== null and count($workspaceIds) > 0) {
            $qb->leftJoin('log.workspace', 'workspace');
            if (count($workspaceIds) == 1) {
                $qb->andWhere("workspace.id = :workspaceId");
                $qb->setParameter('workspaceId', $workspaceIds[0]);
            } else {
                $qb->andWhere("workspace.id IN (:workspaceIds)")->setParameter('workspaceIds', $workspaceIds);
            }
        }

        return $qb;
    }

    private function addConfigurationFilterToQueryBuilder($qb, $configs)
    {
        $actionIndex = 0;
        foreach ($configs as $config) {
            $workspaceId = $config->getWorkspace()->getId();
            $actionRestriction = $config->getActionRestriction();
            if (count($actionRestriction) > 0) {
                foreach ($config->getActionRestriction() as $action) {
                    $qb->orWhere('log.action = :action'.$actionIndex.' AND workspace.id = :workspace'.$workspaceId);
                    $qb->setParameter('action'.$actionIndex, $action);
                    $actionIndex++;
                }
                $qb->setParameter('workspace'.$workspaceId, $workspaceId);
            }

        }

        return $qb;
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
                        $chartData[] = array($lastDay->getTimestamp()*1000, 0);
                        $lastDay->add(new \DateInterval('P1D')); // P1D means a period of 1 day
                    }
                } else {
                    $lastDay = $line['shortDate'];
                }
                $lastDay->add(new \DateInterval('P1D')); // P1D means a period of 1 day

                $chartData[] = array($line['shortDate']->getTimestamp()*1000, intval($line['total']));
            }

            while ($lastDay->getTimestamp() <= $endDay->getTimestamp()) {
                $chartData[] = array($lastDay->getTimestamp()*1000, 0);

                $lastDay->add(new \DateInterval('P1D')); // P1D means a period of 1 day
            }
        }

        return $chartData;
    }

    public function countByDayThroughConfigs($configs, $range)
    {
        if ($configs === null || count($configs) == 0) {
            return null;
        }

        $qb = $this
            ->createQueryBuilder('log')
            ->leftJoin('log.workspace', 'workspace')
            ->select('log.shortDateLog as shortDate, count(log.id) as total')
            ->orderBy('shortDate', 'ASC')
            ->groupBy('shortDate');

        $qb = $this->addConfigurationFilterToQueryBuilder($qb, $configs);

        return $this->extractChartData($qb->getQuery()->getResult(), $range);
    }

    public function countByDayFilteredLogs($action, $range, $userSearch, $actionsRestriction, $workspaceIds = null)
    {
        $qb = $this
            ->createQueryBuilder('log')
            ->select('log.shortDateLog as shortDate, count(log.id) as total')
            ->orderBy('shortDate', 'ASC')
            ->groupBy('shortDate');

        $qb = $this->addActionFilterToQueryBuilder($qb, $action, $actionsRestriction);
        $qb = $this->addDateRangeFilterToQueryBuilder($qb, $range);
        $qb = $this->addUserFilterToQueryBuilder($qb, $userSearch);

        if ($workspaceIds !== null and count($workspaceIds) > 0) {
            $qb = $this->addWorkspaceFilterToQueryBuilder($qb, $workspaceIds);
        }

        return $this->extractChartData($qb->getQuery()->getResult(), $range);
    }

    public function findLogsThroughConfigs($configs, $maxResult = -1)
    {
        if ($configs === null || count($configs) == 0) {
            return null;
        }

        $qb = $this
            ->createQueryBuilder('log')
            ->leftJoin('log.workspace', 'workspace')
            ->orderBy('log.dateLog', 'DESC');

        $qb = $this->addConfigurationFilterToQueryBuilder($qb, $configs);

        if ($maxResult > 0) {
            $qb->setMaxResults($maxResult);
        }

        return $qb->getQuery();
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
        $qb = $this
            ->createQueryBuilder('log')
            ->orderBy('log.dateLog', 'DESC');

        $qb = $this->addActionFilterToQueryBuilder($qb, $action, $actionsRestriction);
        $qb = $this->addDateRangeFilterToQueryBuilder($qb, $range);
        $qb = $this->addUserFilterToQueryBuilder($qb, $userSearch);

        if ($workspaceIds !== null and count($workspaceIds) > 0) {
            $qb = $this->addWorkspaceFilterToQueryBuilder($qb, $workspaceIds);
        }

        if ($maxResult > 0) {
            $qb->setMaxResults($maxResult);
        }

        return $qb->getQuery();
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

    public function findAdminLogsQuery($actionsRestriction)
    {
        $qb = $this
            ->createQueryBuilder('log')
            ->orderBy('log.dateLog', 'DESC');
        $qb = $this->addActionFilterToQueryBuilder($qb, null, $actionsRestriction);

        return $qb->getQuery();
    }

    public function findAdminLogs($actionsRestriction)
    {
        return $this->findAdminLogsQuery($actionsRestriction)->getResult();
    }

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
        $userType = null,
        $childType = null,
        $childAction = null
    )
    {
        $qb = $this
            ->createQueryBuilder('log')
            ->orderBy('log.dateLog', 'DESC')

            ->andWhere('log.action = :action')
            ->setParameter('action', $action)

            ->andWhere('log.dateLog >= :date')
            ->setParameter('date', $date);

        if ($doerId !== null) {
            $qb
                ->leftJoin('log.doer', 'doer')
                ->andWhere('doer.id = :doerId')
                ->setParameter('doerId', $doerId);
        }

        if ($resourceId !== null) {
            $qb
                ->leftJoin('log.resource', 'resource')
                ->andWhere('resource.id = :resourceId')
                ->setParameter('resourceId', $resourceId);
        }

        if ($workspaceId !== null) {
            $qb
                ->leftJoin('log.workspace', 'workspace')
                ->andWhere('workspace.id = :workspaceId')
                ->setParameter('workspaceId', $workspaceId);
        }

        if ($receiverId !== null) {
            $qb
                ->leftJoin('log.receiver', 'receiver')
                ->andWhere('receiver.id = :receiverId')
                ->setParameter('receiverId', $receiverId);
        }

        if ($roleId !== null) {
            $qb
                ->leftJoin('log.role', 'role')
                ->andWhere('role.id = :roleId')
                ->setParameter('roleId', $roleId);
        }

        if ($groupId !== null) {
            $qb
                ->leftJoin('log.receiverGroup', 'receiverGroup')
                ->andWhere('receiverGroup.id = :groupId')
                ->setParameter('groupId', $groupId);
        }

        if ($toolName !== null) {
            $qb
                ->andWhere('log.toolName = :toolName')
                ->setParameter('toolName', $toolName);
        }

        if ($childType !== null) {
            $qb
                ->andWhere('log.childType = :childType')
                ->setParameter('childType', $childType);
        }

        if ($childAction !== null) {
            $qb
                ->andWhere('log.childAction = :childAction')
                ->setParameter('childAction', $childAction);
        }

        $q = $qb->getQuery();
        $logs = $q->getResult();

        return $logs;
    }

    public function topWSByAction ($range, $action, $max)
    {
        $qb = $this
            ->createQueryBuilder('log')
            ->select('ws.id, ws.name, ws.code, count(log.id) AS actions')
            ->leftJoin('log.workspace','ws')
            ->groupBy('ws')
            ->orderBy('actions', 'DESC');

        if ($max >1) {
            $qb->setMaxResults($max);
        }

        $qb = $this->addActionFilterToQueryBuilder($qb, $action, null);
        $qb = $this->addDateRangeFilterToQueryBuilder($qb, $range);
        $query = $qb->getQuery();

        return $query->getResult();
    }

    public function topMediaByAction ($range, $action, $max)
    {
        $qb = $this
            ->createQueryBuilder('log')
            ->select('resource.id, resource.name, count(log.id) AS actions')
            ->leftJoin('log.resource','resource')
            ->leftJoin('log.resourceType','resource_type')
            ->andWhere('resource_type.name=:fileType')
            ->groupBy('resource')
            ->orderBy('actions', 'DESC')
            ->setParameter('fileType','file');

        if ($max >1) {
            $qb->setMaxResults($max);
        }

        $qb = $this->addActionFilterToQueryBuilder($qb, $action, null);
        $qb = $this->addDateRangeFilterToQueryBuilder($qb, $range);
        $query = $qb->getQuery();

        return $query->getResult();
    }

    public function topResourcesByAction ($range, $action, $max)
    {
        $qb = $this
            ->createQueryBuilder('log')
            ->select('resource.id, resource.name, count(log.id) AS actions')
            ->leftJoin('log.resource','resource')
            ->groupBy('resource')
            ->orderBy('actions', 'DESC');

        if ($max >1) {
            $qb->setMaxResults($max);
        }

        $qb = $this->addActionFilterToQueryBuilder($qb, $action, null);
        $qb = $this->addDateRangeFilterToQueryBuilder($qb, $range);
        $query = $qb->getQuery();

        return $query->getResult();
    }

    public function topUsersByAction ($range, $action, $max)
    {
        $qb = $this
            ->createQueryBuilder('log')
            ->select("doer.id, CONCAT(CONCAT(doer.firstName,' '), doer.lastName) AS name, doer.username, count(log.id) AS actions")
            ->leftJoin('log.doer','doer')
            ->groupBy('doer')
            ->orderBy('actions', 'DESC');

        if ($max >1) {
            $qb->setMaxResults($max);
        }

        $qb = $this->addActionFilterToQueryBuilder($qb, $action, null);
        $qb = $this->addDateRangeFilterToQueryBuilder($qb, $range);
        $query = $qb->getQuery();

        return $query->getResult();
    }

    public function activeUsers ()
    {
        $qb = $this
            ->createQueryBuilder('log')
            ->select('COUNT(DISTINCT log.doer) AS users');

        $qb = $this->addActionFilterToQueryBuilder($qb, "user_login", null);

        $query = $qb->getQuery();
        $result = $query->getResult();

        return $result[0]['users'];
    }
}
