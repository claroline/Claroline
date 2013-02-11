<?php
namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use  Claroline\CoreBundle\Entity\User;

class EventRepository extends EntityRepository
{
    /*
    * Get all the user's events by collecting all the workspace where is allowed to write
    */
    public function getAllUserEvents(User $user)
    {
        $dql = "
            SELECT e 
            FROM Claroline\CoreBundle\Entity\Event e
            JOIN e.workspace ws
            WITH ws in (
                SELECT w
                FROM Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace w
                JOIN w.rights wr
                JOIN wr.role r
                JOIN r.users u
                WHERE u.id = :userId
            )
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('userId', $user->getId());

        return $query->getResult();
    }
}