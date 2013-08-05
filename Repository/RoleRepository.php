<?php

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\AbstractRoleSubject;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Tool\Tool;

class RoleRepository extends EntityRepository
{
    /**
     * Returns the roles associated to a workspace.
     *
     * @param AbstractWorkspace $workspace
     *
     * @return array[AbstractWorkspace]
     */
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

    /**
     * Returns the visitor role of a workspace.
     *
     * @param AbstractWorkspace $workspace
     *
     * @return Role
     */
    public function findVisitorRole(AbstractWorkspace $workspace)
    {
        return $this->findBaseWorkspaceRole('VISITOR', $workspace);
    }

    /**
     * Returns the collaborator role of a workspace.
     *
     * @param AbstractWorkspace $workspace
     *
     * @return Role
     */
    public function findCollaboratorRole(AbstractWorkspace $workspace)
    {
        return $this->findBaseWorkspaceRole('COLLABORATOR', $workspace);
    }

    /**
     * Returns the manager role of a workspace.
     *
     * @param AbstractWorkspace $workspace
     *
     * @return Role
     */
    public function findManagerRole(AbstractWorkspace $workspace)
    {
        return $this->findBaseWorkspaceRole('MANAGER', $workspace);
    }

    /**
     * Returns the platform roles of a user.
     *
     * @param User $user
     *
     * @return array[Role]
     */
    public function findPlatformRoles(User $user)
    {
        $dql = "
            SELECT r FROM Claroline\CoreBundle\Entity\Role r
            JOIN r.users u
            WHERE u.id = {$user->getId()} AND r.type != " . Role::WS_ROLE;
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function findByUserAndWorkspace(User $user, AbstractWorkspace $workspace)
    {
        $dql = "
            SELECT r FROM Claroline\CoreBundle\Entity\Role r
            JOIN r.users u
            JOIN r.workspace w
            WHERE u.id = :userId AND w.id = :workspaceId
            ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('userId', $user->getId());
        $query->setParameter('workspaceId', $workspace->getId());

        return $query->getResult();
    }

    public function findByGroupAndWorkspace(Group $group, AbstractWorkspace $workspace)
    {
        $dql = "
            SELECT r FROM Claroline\CoreBundle\Entity\Role r
            JOIN r.groups g
            JOIN r.workspace w
            WHERE g.id = :groupId AND w.id = :workspaceId
            ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('groupId', $group->getId());
        $query->setParameter('workspaceId', $workspace->getId());

        return $query->getResult();
    }

    /**
     * Returns the roles of a user in a workspace.
     *
     * @param User              $user      The subject of the role
     * @param AbstractWorkspace $workspace The workspace the role should be bound to
     *
     * @return null|Role
     */
    public function findWorkspaceRolesForUser(User $user, AbstractWorkspace $workspace)
    {
        $dql = "
            SELECT r FROM Claroline\CoreBundle\Entity\Role r
            JOIN r.workspace ws
            JOIN r.users user
            WHERE ws.guid = '{$workspace->getGuid()}'
            AND r.name != 'ROLE_ADMIN'
            AND user.id = {$user->getId()}
        ";

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    /**
     * Returns the roles which have access to a workspace tool.
     *
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param \Claroline\CoreBundle\Entity\Tool\Tool                   $tool
     */
    public function findByWorkspaceAndTool(AbstractWorkspace $workspace, Tool $tool)
    {
        $dql = "
            SELECT DISTINCT r FROM Claroline\CoreBundle\Entity\Role r
            JOIN r.workspace ws
            JOIN ws.orderedTools ot
            JOIN ot.roles r_2
            JOIN ot.tool tool
            WHERE ws.guid = '{$workspace->getGuid()}'
            AND tool.id = {$tool->getId()}
            AND r.id = r_2.id
            AND r.name != 'ROLE_ADMIN'
        ";

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    /**
     * @todo check and document this method
     */
    public function findByWorkspaceCodeTag($workspaceCode)
    {
        $upperSearch = strtoupper($workspaceCode);

        $dql = "
            SELECT DISTINCT r FROM Claroline\CoreBundle\Entity\Role r
            JOIN r.workspace ws
            LEFT JOIN ws.personalUser pu
            LEFT JOIN Claroline\CoreBundle\Entity\Workspace\RelWorkspaceTag rwt
            WITH rwt.workspace = ws
            LEFT JOIN Claroline\CoreBundle\Entity\Workspace\WorkspaceTag wt
            WITH rwt.tag = wt AND wt.user IS NULL
            LEFT JOIN Claroline\CoreBundle\Entity\Workspace\WorkspaceTagHierarchy wth
            WITH wth.tag = wt AND wth.user IS NULL
            LEFT JOIN wth.parent p
            WHERE pu IS NULL AND (UPPER(ws.code) LIKE :code
            OR UPPER(wt.name) LIKE :code
            OR UPPER(p.name) LIKE :code)
        ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('code', '%'.$upperSearch.'%');

        return $query->getResult();
    }

    private function findBaseWorkspaceRole($roleType, AbstractWorkspace $workspace)
    {
        $dql = "
            SELECT r FROM Claroline\CoreBundle\Entity\Role r
            WHERE r.name = 'ROLE_WS_{$roleType}_{$workspace->getGuid()}'
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getSingleResult();
    }
}
