<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Doctrine\ORM\EntityRepository;

class WorkpaceToolRoleRepository extends EntityRepository
{
    public function findByWorkspace(AbstractWorkspace $workspace)
    {
        $dql = "SELECT wtr, wot, ws FROM Claroline\CoreBundle\Entity\Tool\WorkspaceToolRole wtr
            JOIN wtr.workspaceOrderedTool wot
            JOIN wot.workspace ws
            WHERE ws.id = {$workspace->getId()}";

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }
}
