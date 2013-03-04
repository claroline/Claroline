<?php

namespace Claroline\CoreBundle\Repository;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\AbstractRoleSubject;
use Claroline\CoreBundle\Entity\User;

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
     * Returns the first role found of a user or a group in a workspace.
     *
     * @param AbstractRoleSubject   $subject    The subject of the role
     * @param AbstractWorkspace     $workspace  The workspace the role should be bound to
     * @return null|Role
     */
    public function findWorkspaceRole(AbstractRoleSubject $subject, AbstractWorkspace $workspace)
    {
        $roles = $this->findByWorkspace($workspace);
        foreach ($roles as $role) {
            foreach ($subject->getRoles() as $subjectRole) {
                if ($subjectRole == $role->getName()) {
                    return $role;
                }
            }
        }

        return null;
    }

    /**
     * Returns the unique role of a user in a workspace.
     *
     * @param User              $user The subject of the role
     * @param AbstractWorkspace $workspace  The workspace the role should be bound to
     * @return null|Role
     */
    public function findWorkspaceRoleForUser(User $user, AbstractWorkspace $workspace)
    {
        $dql = "
            SELECT r FROM Claroline\CoreBundle\Entity\Role r
            JOIN r.workspace ws
            JOIN r.users user
            WHERE ws.id = {$workspace->getId()}
            AND r.name != 'ROLE_ADMIN'
            AND user.id = {$user->getId()}
        ";

        $query = $this->_em->createQuery($dql);

        return $query->getOneOrNullResult();
    }
}