<?php

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;

class OrderedToolRepository extends EntityRepository
{
    public function findByWorkspaceAndRoles(AbstractWorkspace $workspace, array $roles)
    {
        $rolesRestriction = "";
        $first = true;
        foreach ($roles as $roleName) {
            if ($first) {
                $first = false;
                $rolesRestriction .= "( r.name like '$roleName'";
            } else {
                $rolesRestriction .= " OR r.name like '$roleName'";
            }
        }
        $rolesRestriction .= " )";

        $dql = "SELECT ot FROM Claroline\CoreBundle\Entity\Tool\OrderedTool ot
            JOIN ot.workspace ws
            JOIN ot.roles r
            WHERE ws.id = {$workspace->getId()}
            AND {$rolesRestriction}
            ORDER BY ot.order";

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }
}
