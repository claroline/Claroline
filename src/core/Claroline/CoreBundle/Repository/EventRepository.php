<?php
namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use  Claroline\CoreBundle\Entity\User;

class EventRepository extends EntityRepository
{
    /*
    * Get all the user's events by collecting all the workspace where is allowed to write
    */
    public function findByUser(User $user , $allDay)
    {
        $dql = "
            SELECT e
            FROM Claroline\CoreBundle\Entity\Event e
            JOIN e.workspace ws
            WITH ws in (
                SELECT w
                FROM Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace w
                JOIN w.roles r
                JOIN r.users u
                WHERE u.id = :userId
            )
            WHERE e.allDay = :allDay
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('userId', $user->getId());
        $query->setParameter('allDay', $allDay);

        return $query->getResult();
    }

    public function findByWorkspaceId($workspaceId,$allDay)
    {
        $dql = "
            SELECT e
            FROM Claroline\CoreBundle\Entity\Event e
            WHERE e.workspace = :workspaceId
            AND e.allDay = :allDay
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspaceId', $workspaceId);
        $query->setParameter('allDay', $allDay);

        return $query->getResult();
    }
}