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
            WHERE (r NOT INSTANCE OF Claroline\CoreBundle\Entity\WorkspaceRole)
        ';
        $query = $this->_em->createQuery($dql);
        $results = $query->getResult();

        return $results;
    }

    public function getWorkspaceRoles(AbstractWorkspace $workspace)
    {
        $dql = "
            SELECT r FROM Claroline\CoreBundle\Entity\Role r
            JOIN r.workspaceRights workspaceRights
            JOIN workspaceRights.workspace ws
            WHERE ws.id = {$workspace->getId()}
            AND r.name != 'ROLE_ANONYMOUS'
            AND r.name != 'ROLE_ADMIN'
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function getCollaboratorRole(AbstractWorkspace $workspace)
    {
        $dql = "
            SELECT r FROM Claroline\CoreBundle\Entity\Role r
            WHERE r.name LIKE 'ROLE_WS_COLLABORATOR_{$workspace->getId()}'
        ";
         $query = $this->_em->createQuery($dql);

         return $query->getSingleResult();
    }

    public function getVisitorRole(AbstractWorkspace $workspace)
    {
        $dql = "
            SELECT r FROM Claroline\CoreBundle\Entity\Role r
            WHERE r.name LIKE 'ROLE_WS_VISITOR_{$workspace->getId()}'
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getSingleResult();
    }

    public function getManagerRole(AbstractWorkspace $workspace)
    {
        $dql = "
            SELECT r FROM Claroline\CoreBundle\Entity\Role r
            WHERE r.name LIKE 'ROLE_WS_MANAGER_{$workspace->getId()}'
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getSingleResult();
    }

    /**
     * Return the role of a user of a group in a workspace.
     *
     * @param Group|User $entity
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     *
     * @return null|Role
     */
    public function getEntityRoleForWorkspace($entity, AbstractWorkspace $workspace)
    {
        $roles = $this->getWorkspaceRoles($workspace);

        foreach ($roles as $role) {
            foreach ($entity->getRoles() as $entityRole) {
                if ($entityRole == $role->getName()) {
                    return $role;
                }
            }
        }

        return null;
    }
}