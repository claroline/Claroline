<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class WorkspaceRepository extends EntityRepository
{
    public function getWorkspacesOfUser(User $user)
    {
        $dql = "
            SELECT w, r, wr FROM Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace w
            JOIN w.rights wr
            JOIN wr.role r
            JOIN r.users u
            WHERE u.id = :userId
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('userId', $user->getId());

        return $query->getResult();
    }

    public function getNonPersonnalWS()
    {
        $dql = "
            SELECT w, r, wr FROM Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace w
            JOIN w.rights wr
            JOIN wr.role r
            JOIN r.users u
            WHERE w.type != 0
        ";

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function getAllWsOfUser(User $user)
    {
        $dql = "
            SELECT w, r, wr FROM Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace w
            JOIN w.rights wr
            JOIN wr.role r
            JOIN r.users u
            WHERE u.id = :userId
            ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('userId', $user->getId());

        return $query->getResult();
    }
}