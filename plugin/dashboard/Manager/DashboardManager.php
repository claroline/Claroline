<?php

namespace Claroline\DashboardBundle\Manager;

use Claroline\CoreBundle\Entity\Log;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Claroline\DashboardBundle\Entity\Dashboard;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.dashboard_manager")
 */
class DashboardManager
{
    protected $em;
    protected $workspaceManager;

    /**
     * @DI\InjectParams({
     *      "em"                 = @DI\Inject("doctrine.orm.entity_manager"),
     *      "workspaceManager"   = @DI\Inject("claroline.manager.workspace_manager")
     * })
     *
     * @param ContainerInterface $container
     * @param EntityManager      $em
     **/
    public function __construct(EntityManager $em, WorkspaceManager $workspaceManager)
    {
        $this->em = $em;
        $this->workspaceManager = $workspaceManager;
    }

    public function getRepository()
    {
        return $this->em->getRepository('ClarolineDashboardBundle:Dashboard');
    }

    public function getClaroLogRepository()
    {
        return $this->em->getRepository('ClarolineCoreBundle:Log\Log');
    }

    /**
     * get all dashboards for a given user.
     */
    public function getAll(User $user)
    {
        return array_map(function ($dashboard) {
            return $this->exportDashboard($dashboard);
        }, $this->getRepository()->findBy(['creator' => $user]));
    }

    /**
     * create a dashboard.
     */
    public function create(User $user, $data)
    {
        $dashboard = new Dashboard();
        $dashboard->setCreator($user);
        $dashboard->setName($data['name']);
        $wId = $data['workspace']['id'];
        $dashboard->setWorkspace($this->workspaceManager->getWorkspaceById($wId));
        $this->em->persist($dashboard);
        $this->em->flush();

        return $this->exportDashboard($dashboard);
    }

    public function update(User $user, Dashboard $dashboard, $data)
    {
        $dashboard->setName($data['name']);
        $wId = $data['workspace']['id'];
        $dashboard->setWorkspace($this->workspaceManager->getWorkspaceById($wId));
        $this->em->persist($dashboard);
        $this->em->flush();

        return $this->exportDashboard($dashboard);
    }

    /**
     * delete a dashboard.
     */
    public function delete(Dashboard $dashboard)
    {
        $this->em->remove($dashboard);
        $this->em->flush();
    }

    /**
     * Compute spent time for each user in a given workspace.
     */
    public function getDashboardWorkspaceSpentTimes(Workspace $workspace, User $user, $all = false)
    {
        $datas = [];
        // user(s) concerned by the query
        if ($all) {
            // get all users involved in the workspace
            $ids = $this->getWorkspaceUsersIds($workspace);
        } else {
            // only the current user
            $ids[] = $user->getId();
        }

        // for each user (ie user ids) -> get first 'workspace-enter' event for the given workspace
        foreach ($ids as $uid) {
            $userSqlSelect = 'SELECT first_name, last_name FROM claro_user WHERE id = :uid';
            $userSqlSelectStmt = $this->em->getConnection()->prepare($userSqlSelect);
            $userSqlSelectStmt->bindValue('uid', $uid);
            $userSqlSelectStmt->execute();
            $userData = $userSqlSelectStmt->fetch();

            // select first "workspace-enter" actions for the given user and workspace
            $selectEnterEventOnThisWorkspace = "SELECT DISTINCT date_log FROM claro_log WHERE workspace_id = :wid AND action = 'workspace-enter' AND doer_id = :uid ORDER BY date_log ASC LIMIT 1";
            $selectEnterEventOnThisWorkspaceStmt = $this->em->getConnection()->prepare($selectEnterEventOnThisWorkspace);
            $selectEnterEventOnThisWorkspaceStmt->bindValue('uid', $uid);
            $selectEnterEventOnThisWorkspaceStmt->bindValue('wid', $workspace->getId());
            $selectEnterEventOnThisWorkspaceStmt->execute();
            $enterOnThisWorksapceDateResult = $selectEnterEventOnThisWorkspaceStmt->fetch();
            $refDate = $enterOnThisWorksapceDateResult['date_log'];

            $total = 0;
            if ($refDate) {
                $total = $this->computeTimeForUserAndWorkspace($refDate, $uid, $workspace->getId(), 0);
            }

            $datas[] = [
              'user' => [
                'id' => $uid,
                'firstName' => $userData['first_name'],
                'lastName' => $userData['last_name'],
              ],
              'time' => $total,
            ];
        }

        return $datas;
    }

    /**
     * Get all ids from users related to a given workspace.
     */
    private function getWorkspaceUsersIds(Workspace $workspace)
    {
        // Select all user(s) belonging to the target workspace (manager and collaborators)...
        $selectUsersIds = 'SELECT DISTINCT cur.user_id FROM claro_user_role cur JOIN claro_role cr ON cr.id = cur.role_id  WHERE cr.workspace_id = :wid';
        $idStmt = $this->em->getConnection()->prepare($selectUsersIds);
        $idStmt->bindValue('wid', $workspace->getId());
        $idStmt->execute();
        $idResults = $idStmt->fetchAll();
        foreach ($idResults as $result) {
            $ids[] = $result['user_id'];
        }

        return $ids;
    }

    /**
     * Search for out and in events on a given wokspace, for a given user and relativly to a date.
     */
    private function computeTimeForUserAndWorkspace($startDate, $userId, $workspaceId, $time)
    {
        // select first "out of this workspace event" (ie "workspace enter" on another workspace)
        $sql = "SELECT date_log FROM claro_log WHERE action LIKE 'workspace-enter' AND doer_id = :uid  AND date_log > :dateLog ORDER BY date_log ASC LIMIT 1";
        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->bindValue('uid', $userId);
        $stmt->bindValue('dateLog', $startDate);
        $stmt->execute();
        $action = $stmt->fetch();

        // if there is an action we can compute time
        if ($action['date_log']) {
            $t1 = strtotime($startDate);
            $t2 = strtotime($action['date_log']);
            $seconds = $t2 - $t1;

            // add time only if bewteen 30s and 2 hours <= totally arbitrary !
            if ($seconds > 5 && ($seconds / 60) <= 120) {
                $time += $seconds;
            }
            // get next "enter the requested workspace enter event"
            $sql = "SELECT date_log FROM claro_log WHERE action LIKE 'workspace-enter' AND doer_id = :uid AND date_log > :dateLog AND workspace_id = :wid ORDER BY date_log ASC LIMIT 1";
            $stmt = $this->em->getConnection()->prepare($sql);
            $stmt->bindValue('uid', $userId);
            $stmt->bindValue('dateLog', $action['date_log']);
            $stmt->bindValue('wid', $workspaceId);
            $stmt->execute();
            $nextEnterEvent = $stmt->fetch();
            // if there is an "enter-workspace" action after the current one recall the method
            if ($nextEnterEvent['date_log']) {
                return $this->computeTimeForUserAndWorkspace($nextEnterEvent['date_log'], $userId, $workspaceId, $time);
            } else {
                return $time;
            }
        }

        return $time;
    }

    /**
     * Export dashboard as array.
     */
    public function exportDashboard(Dashboard $dashboard)
    {
        return [
          'id' => $dashboard->getId(),
          'creatorId' => $dashboard->getCreator()->getId(),
          'name' => $dashboard->getName(),
          'workspace' => $this->workspaceManager->exportWorkspace($dashboard->getWorkspace()),
      ];
    }
}
