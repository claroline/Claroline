<?php

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ResourceRightsRepository extends EntityRepository
{
    public function getDefaultForWorkspace($workspace)
    {
        $dql = "
            SELECT rrw FROM Claroline\CoreBundle\Entity\Workspace\ResourceRights rrw
            JOIN rrw.role role
            JOIN role.workspace ws
            WHERE ws.id = {$workspace->getId()}
            AND rrw.resource IS NULL
            AND role.roleType = 2
            ";

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }
}