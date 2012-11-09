<?php

namespace Claroline\CoreBundle\Repository;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

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
}