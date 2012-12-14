<?php

namespace Claroline\CoreBundle\Repository;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;

class RoleRepository extends NestedTreeRepository
{
    public function getPlatformRoles()
    {
        $dql = '
            SELECT r FROM Claroline\CoreBundle\Entity\Role r
            WHERE (r NOT INSTANCE OF Claroline\CoreBundle\Entity\WorkspaceRole)'
        ;
        $query = $this->_em->createQuery($dql);
        $results = $query->getResult();

        return $results;
    }

    /**
     * Because doctrine does weird things when you go for $workspace->getWorkspaceRoles()...
     */
    public function getWorkspaceRoles(AbstractWorkspace $workspace)
    {
        $dql = "
            SELECT r FROM Claroline\CoreBundle\Entity\Role r
            JOIN r.workspace ws
            WHERE ws.id = {$workspace->getId()}";

        $query = $this->_em->createQuery($dql);
        return $query->getResult();
    }

}