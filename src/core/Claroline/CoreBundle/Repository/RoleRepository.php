<?php

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\AbstractRoleSubject;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Tool\Tool;

class RoleRepository extends EntityRepository
{
    public function findByWorkspace(AbstractWorkspace $workspace)
    {
        $dql = "
            SELECT r FROM Claroline\CoreBundle\Entity\Role r
            JOIN r.workspace ws
            WHERE ws.id = {$workspace->getId()}
        ";

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function findCollaboratorRole(AbstractWorkspace $workspace)
    {
        $dql = "
            SELECT r FROM Claroline\CoreBundle\Entity\Role r
            WHERE r.name = 'ROLE_WS_COLLABORATOR_{$workspace->getId()}'
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getSingleResult();
    }

    public function findVisitorRole(AbstractWorkspace $workspace)
    {
        $dql = "
            SELECT r FROM Claroline\CoreBundle\Entity\Role r
            WHERE r.name = 'ROLE_WS_VISITOR_{$workspace->getId()}'
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getSingleResult();
    }

    public function findPlatformRoles(User $user)
    {
        $dql = "
            SELECT r FROM Claroline\CoreBundle\Entity\Role r
            JOIN r.users u
            WHERE u.id = {$user->getId()} AND r.type != " . Role::WS_ROLE;
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function findManagerRole(AbstractWorkspace $workspace)
    {
        $dql = "
            SELECT r FROM Claroline\CoreBundle\Entity\Role r
            WHERE r.name = 'ROLE_WS_MANAGER_{$workspace->getId()}'
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

    /**
     * Returns the list of role for a workspace and a tool
     *
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param \Claroline\CoreBundle\Entity\Tool\Tool $tool
     */
    public function findByWorkspaceAndTool(AbstractWorkspace $workspace, Tool $tool)
    {
        $dql = "
            SELECT DISTINCT r FROM Claroline\CoreBundle\Entity\Role r
            JOIN r.workspace ws
            JOIN ws.workspaceOrderedTools wot
            JOIN wot.workspaceToolRoles wtr
            JOIN wtr.role r_2
            JOIN wot.tool tool
            WHERE ws.id = {$workspace->getId()}
            AND tool.id = {$tool->getId()}
            AND r.id = r_2.id
            AND r.name != 'ROLE_ADMIN'";

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function findByWorkspaceCode($workspaceCode)
    {
        $upperSearch = strtoupper($workspaceCode);

        $dql = "
            SELECT DISTINCT r FROM Claroline\CoreBundle\Entity\Role r
            JOIN r.workspace ws
            WHERE UPPER(ws.code) LIKE :code
        ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('code', '%'.$upperSearch.'%');

        return $query->getResult();
    }
}