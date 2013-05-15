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
        if ($action == "'resource_all'") {
            $action = "'resource_create',
            'resource_move',
            'resource_read',
            'resource_export',
            'resource_delete',
            'resource_update',
            'resource_shortcut',
            'resource_child_update',
            ";
        } else if ($action == "'ws_role_all'") {
            $action = "'ws_role_create',
                'ws_role_delete',
                'ws_role_update',
                'ws_role_change_right',
                'ws_role_subscribe_user',
                'ws_role_unsubscribe_user',
                'ws_role_subscribe_group',
                'ws_role_unsubscribe_group'";
        } else if ($action == "'group_all'") {
            $action = "'group_add_user', 'group_create', 'group_delete', 'group_remove_user', 'group_update'";
        } else if ($action == "'user_all'") {
            $action = "'user_create', 'user_delete', 'user_login', 'user_update'";
        } else if ($action == "'workspace_all'") {
            $action = "'workspace_create', 'workspace_delete', 'workspace_update'";
        }

        if ($action === null or $action == 'all') {
            $qb->andWhere("log.action IN (".$this->actionsRestrictionToString($actionsRestriction).")");
        } else {
            $qb->andWhere("log.action IN (".$action.")");
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
            $upperUserSearch = preg_replace('/\s+/', ' ',$upperUserSearch);

            $qb->leftJoin('log.doer', 'doer');
            $qb->andWhere(
                $qb->expr()->orx(
                   $qb->expr()->like('UPPER(doer.lastName)', ':userSearch'),
                   $qb->expr()->like('UPPER(doer.firstName)', ':userSearch'),
                   $qb->expr()->like('UPPER(doer.username)', ':userSearch'),
                   $qb->expr()->like("CONCAT(UPPER(doer.firstName), ' ', UPPER(doer.lastName))", ':userSearch'),
                   $qb->expr()->like("CONCAT(UPPER(doer.lastName), ' ', UPPER(doer.firstName))", ':userSearch')
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
            if(count($workspaceIds) == 1) {
                $qb->andWhere("workspace.id = :workspaceId");
                $qb->setParameter('workspaceId', $workspaceIds[0]);
            } else {
                $restriction = "";
                $first = true;
                foreach ($workspaceIds as $workspaceId) {
                    if ($first) {
                        $first = false;
                        $restriction .= "'".$workspaceId."'";
                    } else {
                        $restriction .= ", '".$workspaceId."'";
                    }
                }
                $qb->andWhere("workspace.id IN (".$restriction.")");
            }

        }

        return $qb;
    }

    public function countByDayFilteredLogs($action, $range, $userSearch, $actionsRestriction, $workspaceIds = null)
    {
        $qb = $this
            ->createQueryBuilder('log')
            ->select('SUBSTRING(log.dateLog, 1, 10) AS shortDate, count(log) as total')
            ->orderBy('log.dateLog', 'ASC')
            ->groupBy('shortDate');

        $qb = $this->addActionFilterToQueryBuilder($qb, $action, $actionsRestriction);
        $qb = $this->addDateRangeFilterToQueryBuilder($qb, $range);
        $qb = $this->addUserFilterToQueryBuilder($qb, $userSearch);

        if ($workspaceIds !== null and count($workspaceIds) > 0) {
            $qb = $this->addWorkspaceFilterToQueryBuilder($qb, $workspaceIds);
        }

        $query = $qb->getQuery();

        $result = $query->getResult();
        $chartData = array();
        if (count($result) > 0) {
            //We send an array indexed by date dans contains count
            $lastDay = null;
            $endDay = null;
            if ($range !== null and count($range) == 2) {
                $startDate = new \DateTime();
                $startDate->setTimestamp($range[0]);
                $lastDay = $startDate->format('Y-m-d');

                $endDate = new \DateTime();
                $endDate->setTimestamp($range[1]);
                $endDay = $endDate->format('Y-m-d');
            }

            foreach ($result as $line) {
                if ($lastDay !== null) {
                    while (strtotime($lastDay) < strtotime($line['shortDate'])) {
                        $chartData[] = array($lastDay, 0);

                        $date = new \DateTime($lastDay);
                        $date->add(new \DateInterval('P1D')); // P1D means a period of 1 day
                        $lastDay = $date->format('Y-m-d');
                    }
                } else {
                    $lastDay =  $line['shortDate'];
                }

                $date = new \DateTime($lastDay);
                $date->add(new \DateInterval('P1D')); // P1D means a period of 1 day
                $lastDay = $date->format('Y-m-d');

                $chartData[] = array($line['shortDate'], intval($line['total']));
            }

            while (strtotime($lastDay) <= strtotime($endDay)) {
                $chartData[] = array($lastDay, 0);

                $date = new \DateTime($lastDay);
                $date->add(new \DateInterval('P1D')); // P1D means a period of 1 day
                $lastDay = $date->format('Y-m-d');
            }
        }

        return $chartData;
    }

    public function findFilteredLogsQuery($action, $range, $userSearch, $actionsRestriction, $workspaceIds = null, $maxResult = -1)
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
        return $this->findFilteredLogsQuery($action, $range, $userSearch, $actionsRestriction, $workspaceIds)->getResult();
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

    //findByUserIdAndActionAndAfterDate
    public function findByUserIdAndActionAndAfterDate($userId, $action, $date, $resourceId = null, $workspaceId = null, $receiverId = null, $roleId = null, $groupId = null)
    {
        // var_dump($userId);
        // var_dump($action);
        // var_dump($date);
        // if ($resourceId) {
        //     var_dump($resourceId);
        // }
        // if ($workspaceId) {
        //     var_dump($workspaceId);
        // }
        // if ($receiverId) {
        //     var_dump($receiverId);
        // }
        // if ($roleId) {
        //     var_dump($roleId);
        // }
        // if ($groupId) {
        //     var_dump($groupId);
        // }

        $qb = $this
            ->createQueryBuilder('log')
            ->orderBy('log.dateLog', 'DESC')
            ->leftJoin('log.doer', 'doer')
            
            ->andWhere('log.action = :action')
            ->setParameter('action', $action)
            
            ->andWhere('doer.id = :userId')
            ->setParameter('userId', $userId)

            ->andWhere('log.dateLog >= :date')
            ->setParameter('date', $date);

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

        $q = $qb->getQuery();
        $logs = $q->getResult();

        // foreach ($logs as $log) {
        //     //var_dump($log->getDetails()['resource']['name']);
        //     //var_dump($log->getDetails()['role']['name']);
        // }

        return $logs;
    }
}