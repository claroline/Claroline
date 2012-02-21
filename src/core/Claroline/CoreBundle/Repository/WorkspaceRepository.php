<?php

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\User;

class WorkspaceRepository extends EntityRepository
{
    public function getWorkspacesOfUser(User $user)
    {
        $dql = "
            SELECT w FROM Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace w
            JOIN w.roles wr JOIN wr.users u WHERE u.id = '{$user->getId()}' 
        ";
        $query = $this->_em->createQuery($dql);
        
        return $query->getResult();
    }
}