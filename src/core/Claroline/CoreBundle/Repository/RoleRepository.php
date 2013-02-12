<?php

namespace Claroline\CoreBundle\Repository;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;

class RoleRepository extends NestedTreeRepository
{
    public function findByWorkspace(AbstractWorkspace $workspace)
    {
        $dql = "
            SELECT r FROM Claroline\CoreBundle\Entity\Role r
            JOIN r.workspace ws
            WHERE ws.id = {$workspace->getId()}
            AND r.name != 'ROLE_ADMIN'
        ";

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function findCollaboratorRole(AbstractWorkspace $workspace)
    {
        $dql = "
            SELECT r FROM Claroline\CoreBundle\Entity\Role r
            WHERE r.name LIKE 'ROLE_WS_COLLABORATOR_{$workspace->getId()}'
        ";
         $query = $this->_em->createQuery($dql);

         return $query->getSingleResult();
    }

    public function findVisitorRole(AbstractWorkspace $workspace)
    {
        $dql = "
            SELECT r FROM Claroline\CoreBundle\Entity\Role r
            WHERE r.name LIKE 'ROLE_WS_VISITOR_{$workspace->getId()}'
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getSingleResult();
    }

    public function findManagerRole(AbstractWorkspace $workspace)
    {
        $dql = "
            SELECT r FROM Claroline\CoreBundle\Entity\Role r
            WHERE r.name LIKE 'ROLE_WS_MANAGER_{$workspace->getId()}'
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getSingleResult();
    }

    /**
     * Return the role of a user or a group in a workspace.
     *
     * @todo change this name or move this.
     * @param Group|User $entity
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     *
     * @return null|Role
     */
    public function getEntityRoleForWorkspace($entity, AbstractWorkspace $workspace)
    {
        $roles = $this->findByWorkspace($workspace);

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