<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AgendaBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class EventRepository extends EntityRepository
{
    /*
    * Get all the user's events by collecting all the workspace where is allowed to write
    */
    public function findByUser(User $user, $isTask)
    {
        $dql = "
            SELECT e
            FROM Claroline\AgendaBundle\Entity\Event e
            JOIN e.workspace ws
            WITH ws in (
                SELECT w
                FROM Claroline\CoreBundle\Entity\Workspace\Workspace w
                JOIN w.roles r
                JOIN r.users u
                WHERE u.id = :userId
                AND (
                    EXISTS (
                        SELECT ot
                        FROM Claroline\CoreBundle\Entity\Tool\OrderedTool ot
                        JOIN ot.tool t
                        JOIN t.maskDecoders tm
                        JOIN ot.rights otr
                        WHERE ot.workspace = w
                        AND t.name = :agenda
                        AND otr.role = r
                        AND tm.name = :open
                        AND BIT_AND(otr.mask, tm.value) = tm.value
                    )
                    OR r.name = CONCAT('ROLE_WS_MANAGER_', w.guid)
                )
            )
            WHERE e.isTask = :isTask
            ORDER BY e.start DESC
        ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('userId', $user->getId());
        $query->setParameter('isTask', $isTask);
        $query->setParameter('agenda', 'agenda_');
        $query->setParameter('open', 'open');

        return $query->getResult();
    }

    /*
   * Get all the events and the tasks of the user for all the workspace where is allowed to write
   */
    public function findEventsAndTasksOfWorkspaceForTheUser(User $user)
    {
        $dql = "
            SELECT e
            FROM Claroline\AgendaBundle\Entity\Event e
            JOIN e.workspace ws
            WITH ws in (
                SELECT w
                FROM Claroline\CoreBundle\Entity\Workspace\Workspace w
                LEFT JOIN w.roles r
                LEFT JOIN r.users u
                LEFT JOIN r.groups gr
                LEFT JOIN gr.users gru
                WHERE (u.id = :userId OR gru = :userId)
                AND (
                    EXISTS (
                        SELECT ot
                        FROM Claroline\CoreBundle\Entity\Tool\OrderedTool ot
                        JOIN ot.tool t
                        JOIN t.maskDecoders tm
                        JOIN ot.rights otr
                        WHERE ot.workspace = w
                        AND t.name = :agenda
                        AND otr.role = r
                        AND tm.name = :open
                        AND BIT_AND(otr.mask, tm.value) = tm.value
                    )
                    OR r.name = CONCAT('ROLE_WS_MANAGER_', w.guid)
                )
            )
            ORDER BY e.start DESC
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('userId', $user->getId());
        $query->setParameter('agenda', 'agenda_');
        $query->setParameter('open', 'open');

        return $query->getResult();
    }

    /*
     * Find all the workspaces where the user is allowed to edit the agenda
     */
    public function findEditableUserWorkspaces(User $user)
    {
        $dql = "
            SELECT w
            FROM Claroline\CoreBundle\Entity\Workspace\Workspace w
            JOIN w.roles r
            JOIN r.users u
            WHERE u.id = :userId
            AND (
                EXISTS (
                    SELECT ot
                    FROM Claroline\CoreBundle\Entity\Tool\OrderedTool ot
                    JOIN ot.tool t
                    JOIN t.maskDecoders tm
                    JOIN ot.rights otr
                    WHERE ot.workspace = w
                    AND t.name = :agenda
                    AND otr.role = r
                    AND tm.name = :edit
                    AND BIT_AND(otr.mask, tm.value) = tm.value
                )
                OR r.name = CONCAT('ROLE_WS_MANAGER_', w.guid)
            )
        ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('userId', $user->getId());
        $query->setParameter('agenda', 'agenda_');
        $query->setParameter('edit', 'edit');

        return $query->getResult();
    }

    /**
     * @param User $user
     * @param bool $isTask
     *
     * @return array
     */
    public function findDesktop(User $user, $isTask)
    {
        $dql = '
            SELECT e
            FROM Claroline\AgendaBundle\Entity\Event e
            WHERE e.workspace is NULL
            AND e.user =:userId
            ORDER BY e.start DESC
            ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('userId', $user->getId());

        return $query->getResult();
    }

    public function findByWorkspaceId($workspaceId, $isTask = null, $limit = null)
    {
        $qb = $this->createQueryBuilder('e')
            ->select('e')
            ->where('e.workspace = :workspaceId')
            ->orderBy('e.end', 'DESC');

        if ($isTask !== null) {
            $qb->andWhere('e.isTask = :isTask');
            $qb->setParameter('isTask', $isTask);
        }

        $query = $qb->setParameter('workspaceId', $workspaceId)
            ->getQuery();

        if ($limit > 0) {
            $query->setMaxResults($limit);
        }

        return $query->getResult();
    }

    public function findLastEventsOrTasksByWorkspaceId($workspaceId, $isTask)
    {
        $qb = $this->createQueryBuilder('e')
            ->select('e')
            ->where('e.workspace = :workspaceId')
            ->andWhere('e.isTask = :isTask')
            ->andWhere('e.isTaskDone = false')
            ->orderBy('e.start', 'ASC');

        if (!$isTask) {
            $qb->andWhere('e.end > :time');
            $qb->setParameter('time', time());
        }

        return $qb->setParameter('workspaceId', $workspaceId)
            ->setParameter('isTask', $isTask)
            ->getQuery()
            ->getResult();
    }

    public function findByUserWithoutAllDay(User $user, $limit)
    {
        $dql = "
            SELECT e
            FROM Claroline\AgendaBundle\Entity\Event e
            JOIN e.workspace ws
            WITH ws in (
                SELECT w
                FROM Claroline\CoreBundle\Entity\Workspace\Workspace w
                JOIN w.roles r
                JOIN r.users u
                WHERE u.id = :userId
            )
            ORDER BY e.end DESC
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('userId', $user->getId());
        if ($limit > 0) {
            $query->setMaxResults($limit);
        }

        return $query->getResult();
    }

    public function getDesktopTaskNotDone(User $user)
    {
        $dql = "
            SELECT e
            FROM Claroline\AgendaBundle\Entity\Event e
            WHERE e.user = :userId
            AND e.isTask = true
            AND e.isTaskDone = false
            AND e.workspace is null
            ORDER BY e.start ASC
        ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('userId', $user->getId());

        return $query->getResult();
    }

    public function getWorkspaceTaskNotDone(User $user)
    {
        $dql = "
            SELECT e
            FROM Claroline\AgendaBundle\Entity\Event e
            JOIN e.workspace ws
            WITH ws in (
                SELECT w
                FROM Claroline\CoreBundle\Entity\Workspace\Workspace w
                LEFT JOIN w.roles r
                LEFT JOIN r.users u
                LEFT JOIN r.groups gr
                LEFT JOIN gr.users gru
                WHERE (u.id = :userId OR gru = :userId)
            )
            WHERE e.isTask = true
            AND e.isTaskDone = false
            ORDER BY e.start ASC
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('userId', $user->getId());

        return $query->getResult();
    }

    public function getFutureDesktopEvents(User $user, $limit = null)
    {
        $dql = "
            SELECT e
            FROM Claroline\AgendaBundle\Entity\Event e
            WHERE e.end > :dateEnd
            AND e.user = :userId
            AND e.isTask = false
            AND e.workspace is null
            AND e.isTaskDone = false
            ORDER BY e.start ASC
        ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('userId', $user->getId());
        $query->setParameter('dateEnd', time());
        if ($limit) {
            $query->setMaxResults($limit);
        }

        return $query->getResult();
    }

    public function getFutureWorkspaceEvents($user)
    {
        $dql = "
            SELECT w.id
            FROM Claroline\CoreBundle\Entity\Workspace\Workspace w
            JOIN w.roles r
            LEFT JOIN r.users u
            LEFT JOIN r.groups gr
            LEFT JOIN gr.users gru
            WHERE u.id = :userId
            OR gru = :userId
        ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('userId', $user->getId());
        $wids = $query->getResult();
        $wids = array_map('current', $wids);

        $dql = "
            SELECT e
            FROM Claroline\AgendaBundle\Entity\Event e
            JOIN e.workspace ws
            WITH ws in (:wids)
            WHERE e.isTask = false
            AND e.isTaskDone = false
            AND e.end > :endDate
            ORDER BY e.start ASC
        ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('endDate', time());
        $query->setParameter('wids', $wids);

        return $query->getResult();
    }
}
