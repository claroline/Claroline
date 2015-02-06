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

use Doctrine\ORM\EntityRepository;
use  Claroline\CoreBundle\Entity\User;

class EventRepository extends EntityRepository
{
    /*
    * Get all the user's events by collecting all the workspace where is allowed to write
    */
    public function findByUser(User $user , $isTask)
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
            WHERE e.isTask = :isTask
            ORDER BY e.start DESC
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('userId', $user->getId());
        $query->setParameter('isTask', $isTask);

        return $query->getResult();
    }

    /**
     * @param User $user
     * @param boolean $allDay
     * @return array
     */
    public function findDesktop(User $user, $isTask)
    {
        $dql = '
            SELECT e
            FROM Claroline\AgendaBundle\Entity\Event e
            WHERE e.workspace is NULL
            AND e.isTask = :isTask
            AND e.user =:userId
            ORDER BY e.start DESC
            ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('isTask', $isTask);
        $query->setParameter('userId', $user->getId());

        return $query->getResult();
    }

    public function findByWorkspaceId($workspaceId, $isTask, $limit = null)
    {
        $dql = "
            SELECT e
            FROM Claroline\AgendaBundle\Entity\Event e
            WHERE e.workspace = :workspaceId
            AND e.isTask = :isTask
            ORDER BY e.end DESC
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspaceId', $workspaceId);
        $query->setParameter('isTask', $isTask);
        if ($limit > 0) {
            $query->setMaxResults($limit);
        }

        return $query->getResult();
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
}
