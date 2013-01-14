<?php

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\User;

class WorkspaceRightsRepository extends EntityRepository
{
    /**
     * Gets the workspace rights of a user !
     *
     * @param \Claroline\CoreBundle\Entity\User $user
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     *
     * @return type
     */
    public function getRights($roles, AbstractWorkspace $workspace)
    {
        if(count($roles) == 0){
            throw new \RuntimeException("The role array cannot be empty for the getRights method in the ResourceRightRepository");
        }

        $dql = "
            SELECT
                MAX(wsr.canView) as canView,
                MAX(wsr.canEdit) as canEdit,
                MAX(wsr.canManage) as canManage,
                MAX(wsr.canDelete) as canDelete
            FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceRights wsr

            JOIN wsr.role role
            JOIN wsr.workspace workspace
            LEFT JOIN role.users user

            WHERE ";


        $i=0;

        foreach($roles as $role){
            if($i!=0){
                $dql.= " OR workspace.id = {$workspace->getId()} AND role.name LIKE '{$role}'";
            } else {
                $dql.= " workspace.id = {$workspace->getId()} AND role.name LIKE '{$role}'";
                $i++;
            }
        }


       $query = $this->_em->createQuery($dql);

       return $query->getSingleResult();
    }
}