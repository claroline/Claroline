<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\AdminTool;
use Claroline\CoreBundle\Entity\Tool\ToolMaskDecoder;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class RoleRepository extends EntityRepository
{
    /**
     * Returns the roles associated to a workspace.
     *
     * @param Workspace $workspace
     *
     * @return array[Workspace]
     */
    public function findByWorkspace(Workspace $workspace, $orderedBy = 'id', $order = 'ASC')
    {
        $dql = "
            SELECT r FROM Claroline\CoreBundle\Entity\Role r
            JOIN r.workspace ws
            WHERE ws.id = :workspaceId
            ORDER BY r.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspaceId', $workspace->getId());

        return $query->getResult();
    }

    /**
     * Returns the searched roles associated to a workspace.
     *
     * @param Workspace $workspace
     *
     * @return array[Workspace]
     */
    public function findByWorkspaceAndSearch(
        Workspace $workspace,
        $search = '',
        $orderedBy = 'id',
        $order = 'ASC'
    ) {
        $dql = "
            SELECT r FROM Claroline\CoreBundle\Entity\Role r
            JOIN r.workspace ws
            WHERE ws.id = :workspaceId
            AND UPPER(r.translationKey) LIKE :search
            ORDER BY r.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspaceId', $workspace->getId());
        $upperSearch = strtoupper($search);
        $query->setParameter('search', "%{$upperSearch}%");

        return $query->getResult();
    }

    /**
     * Returns the visitor role of a workspace.
     *
     * @param Workspace $workspace
     *
     * @return Role
     */
    public function findVisitorRole(Workspace $workspace)
    {
        return $this->findBaseWorkspaceRole('VISITOR', $workspace);
    }

    /**
     * Returns the collaborator role of a workspace.
     *
     * @param Workspace $workspace
     *
     * @return Role
     */
    public function findCollaboratorRole(Workspace $workspace)
    {
        return $this->findBaseWorkspaceRole('COLLABORATOR', $workspace);
    }

    /**
     * Returns the manager role of a workspace.
     *
     * @param Workspace $workspace
     *
     * @return Role
     */
    public function findManagerRole(Workspace $workspace)
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
            WHERE u.id = {$user->getId()} AND r.type = ".Role::PLATFORM_ROLE;
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    /**
     * Returns all platform roles.
     *
     * @param bool $includeRoleUser
     * @param bool $getBuilder
     *
     * @return array|\Doctrine\ORM\QueryBuilder
     */
    public function findAllPlatformRoles($includeRoleUser = true, $getBuilder = false)
    {
        $queryBuilder = $this
            ->createQueryBuilder('role')
            ->andWhere('role.type = :roleType')
            ->setParameter('roleType', Role::PLATFORM_ROLE);
        $queryBuilder->andWhere($queryBuilder->expr()->not($queryBuilder->expr()->eq('role.name', '?1')))
            ->setParameter(1, 'ROLE_ANONYMOUS');

        if (!$includeRoleUser) {
            $queryBuilder->andWhere($queryBuilder->expr()->not($queryBuilder->expr()->eq('role.name', '?2')))
                ->setParameter(2, 'ROLE_USER');
        }

        if ($getBuilder) {
            return $queryBuilder;
        }

        $query = $queryBuilder->getQuery();

        return $query->getResult();
    }

    public function findAllPlatformRoleNamesAndKeys($includeRoleUser = true)
    {
        $qb = $this->findAllPlatformRoles($includeRoleUser, true);
        $qb->select('role.name', 'role.translationKey');

        return $qb->getQuery()->getResult();
    }

    public function findByUserAndWorkspace(User $user, Workspace $workspace)
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

    public function findByGroupAndWorkspace(Group $group, Workspace $workspace)
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
     * @param User      $user      The subject of the role
     * @param Workspace $workspace The workspace the role should be bound to
     *
     * @return null|Role
     */
    public function findWorkspaceRolesForUser(User $user, Workspace $workspace)
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

    public function findRolesByWorkspaceAndRoleNames(
        Workspace $workspace,
        array $roles
    ) {
        $dql = "
            SELECT r FROM Claroline\CoreBundle\Entity\Role r
            JOIN r.workspace w
            WHERE w = :workspace
            AND r.name IN (:roles)
        ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);
        $query->setParameter('roles', $roles);

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
            LEFT JOIN Claroline\CoreBundle\Entity\Workspace\RelWorkspaceTag rwt
            WITH rwt.workspace = ws
            LEFT JOIN Claroline\CoreBundle\Entity\Workspace\WorkspaceTag wt
            WITH rwt.tag = wt AND wt.user IS NULL
            LEFT JOIN Claroline\CoreBundle\Entity\Workspace\WorkspaceTagHierarchy wth
            WITH wth.tag = wt AND wth.user IS NULL
            LEFT JOIN wth.parent p
            WHERE ws.displayable = true AND (UPPER(ws.code) LIKE :code
            OR UPPER(wt.name) LIKE :code
            OR UPPER(p.name) LIKE :code)
        ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('code', '%'.$upperSearch.'%');

        return $query->getResult();
    }

    private function findBaseWorkspaceRole($roleType, Workspace $workspace)
    {
        $dql = "
            SELECT r FROM Claroline\CoreBundle\Entity\Role r
            WHERE r.name = 'ROLE_WS_{$roleType}_{$workspace->getGuid()}'
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getOneOrNullResult();
    }

    public function searchByName($search)
    {
        $upperSearch = strtoupper(trim($search));

        $dql = "
            SELECT r
            FROM Claroline\CoreBundle\Entity\Role r
            WHERE UPPER(r.name) LIKE :search
        ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('search', "%{$upperSearch}%");

        return $query->getResult();
    }

    public function findAll()
    {
        $dql = "
            SELECT r, w
            FROM Claroline\CoreBundle\Entity\Role r
            LEFT JOIN r.workspace w
        ";

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function findPlatformNonAdminRoles($includeAnonymous = false)
    {
        $queryBuilder = $this
            ->createQueryBuilder('role')
            ->andWhere('role.type = :roleType')
            ->setParameter('roleType', Role::PLATFORM_ROLE);
        if (!$includeAnonymous) {
            $queryBuilder->andWhere($queryBuilder->expr()->not($queryBuilder->expr()->eq('role.name', '?1')))
                ->setParameter(1, 'ROLE_ANONYMOUS');
        }
        $queryBuilder->andWhere($queryBuilder->expr()->not($queryBuilder->expr()->eq('role.name', '?2')))
            ->setParameter(2, 'ROLE_ADMIN');
        $query = $queryBuilder->getQuery();

        return $query->getResult();
    }

    public function findAllWhereWorkspaceIsDisplayable()
    {
        $dql = "
            SELECT r, w
            FROM Claroline\CoreBundle\Entity\Role r
            JOIN r.workspace w
            WHERE w.displayable = true
        ";

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function findAllWhereWorkspaceIsDisplayableAndInList(array $workspaces)
    {
        if (count($workspaces) === 0) {
            return [];
        } else {
            $dql = "
                SELECT r, w
                FROM Claroline\CoreBundle\Entity\Role r
                JOIN r.workspace w
                WHERE w.displayable = true
                AND w IN (:workspaces)
            ";

            $query = $this->_em->createQuery($dql);
            $query->setParameter('workspaces', $workspaces);

            return $query->getResult();
        }
    }

    public function findByAdminTool(AdminTool $adminTool)
    {
        $dql = "
            SELECT r FROM Claroline\CoreBundle\Entity\Role r
            JOIN r.adminTools t
            WHERE t.id = :id
        ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('id', $adminTool->getId());

        return $query->getResult();
    }

    public function findRolesWithRightsByResourceNode(
        ResourceNode $resourceNode,
        $executeQuery = true
    ) {
        $dql = '
            SELECT r
            FROM Claroline\CoreBundle\Entity\Role r
            WHERE EXISTS (
                SELECT rr
                FROM Claroline\CoreBundle\Entity\Resource\ResourceRights rr
                WHERE rr.role = r
                AND rr.resourceNode = :resourceNode
                AND MOD(rr.mask, 2) = 1
            )
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('resourceNode', $resourceNode);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findRoleByWorkspaceCodeAndTranslationKey(
        $workspaceCode,
        $translationKey,
        $executeQuery = true
    ) {
        $dql = '
            SELECT r
            FROM Claroline\CoreBundle\Entity\Role r
            INNER JOIN r.workspace w
            WHERE w.code = :code
            AND r.translationKey = :key
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('code', $workspaceCode);
        $query->setParameter('key', $translationKey);

        return $executeQuery ? $query->getOneOrNullResult() : $query;
    }

    public function findRolesByWorkspaceCodeAndTranslationKey(
        $workspaceCode,
        $translationKey,
        $executeQuery = true
    ) {
        $dql = '
            SELECT r
            FROM Claroline\CoreBundle\Entity\Role r
            INNER JOIN r.workspace w
            WHERE w.code = :code
            AND r.translationKey = :key
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('code', $workspaceCode);
        $query->setParameter('key', $translationKey);

        return $executeQuery ? $query->getResult() : $query;
    }

    /**
     * Returns all non-platform roles of a user.
     *
     * @param User $user The subject of the role
     *
     * @return array[Role]|query
     */
    public function findNonPlatformRolesForUser(User $user, $executeQuery = true)
    {
        $dql = '
            SELECT r
            FROM Claroline\CoreBundle\Entity\Role r
            JOIN r.workspace w
            JOIN r.users u
            WHERE w.creator != :user
            AND u = :user
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);

        return $executeQuery ? $query->getResult() : $query;
    }

    /**
     * Returns all user-type roles.
     *
     * @param bool $executeQuery
     *
     * @return array[Role]|query
     */
    public function findAllUserRoles($executeQuery = true)
    {
        $dql = '
            SELECT r
            FROM Claroline\CoreBundle\Entity\Role r
            WHERE r.type = :type
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('type', Role::USER_ROLE);

        return $executeQuery ? $query->getResult() : $query;
    }

    /**
     * Returns user-type role of an user.
     *
     * @param string $username
     * @param bool   $executeQuery
     *
     * @return Role[]|Query
     */
    public function findUserRoleByUsername($username, $executeQuery = true)
    {
        $query = $this->_em
            ->createQuery('
                SELECT r
                FROM Claroline\CoreBundle\Entity\Role r
                WHERE r.type = :type
                AND r.name = :name
                AND r.translationKey = :key
            ')
            ->setParameter('type', Role::USER_ROLE)
            ->setParameter('name', 'ROLE_USER_'.strtoupper($username))
            ->setParameter('key', $username);

        return $executeQuery ? $query->getOneOrNullResult() : $query;
    }

    public function findUserRolesByTranslationKeys(array $keys, $executeQuery = true)
    {
        $dql = '
            SELECT r
            FROM Claroline\CoreBundle\Entity\Role r
            WHERE r.type = :type
            AND r.translationKey IN (:keys)
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('type', Role::USER_ROLE);
        $query->setParameter('keys', $keys);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findWorkspaceRoleWithToolAccess(Workspace $workspace)
    {
        $dql = '
            SELECT r
            FROM Claroline\CoreBundle\Entity\Role r
            WHERE r.workspace = :workspace
            AND (
                r.name = :managerRoleName
                OR EXISTS (
                    SELECT ot
                    FROM Claroline\CoreBundle\Entity\Tool\OrderedTool ot
                    JOIN ot.rights otr
                    JOIN otr.role otrr
                    WHERE ot.workspace = :workspace
                    AND otrr = r
                    AND BIT_AND(otr.mask, :openValue) = :openValue
                )
            )
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);
        $query->setParameter('managerRoleName', 'ROLE_WS_MANAGER_'.$workspace->getGuid());
        $query->setParameter('openValue', ToolMaskDecoder::$defaultValues['open']);

        return $query->getResult();
    }

    public function findWorkspaceRoleByNameOrTranslationKey(
        Workspace $workspace,
        $translationKey,
        $executeQuery = true
    ) {
        $dql = '
            SELECT r
            FROM Claroline\CoreBundle\Entity\Role r
            WHERE r.workspace = :workspace
            AND (
                r.name = :roleName
                OR UPPER(r.translationKey) = :key
            )
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);
        $query->setParameter('key', strtoupper($translationKey));
        $query->setParameter(
            'roleName',
            'ROLE_WS_'.strtoupper($translationKey).'_'.$workspace->getGuid()
        );

        return $executeQuery ? $query->getOneOrNullResult() : $query;
    }

    public function findRoleByUserAndRoleName(User $user, $roleName, $executeQuery = true)
    {
        $dql = '
            SELECT r
            FROM Claroline\CoreBundle\Entity\Role r
            JOIN r.users u
            WHERE r.name = :roleName
            AND u = :user
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);
        $query->setParameter('roleName', $roleName);

        return $executeQuery ? $query->getOneOrNullResult() : $query;
    }

    /**
     * Returns all workspace roles of an user.
     *
     * @param User $user The subject of the role
     *
     * @return Role[]
     */
    public function findWorkspaceRolesByUser(User $user, $executeQuery = true)
    {
        $dql = '
            SELECT r
            FROM Claroline\CoreBundle\Entity\Role r
            JOIN r.users u
            WHERE u = :user
            AND r.type = :type
            AND r.workspace IS NOT NULL
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('type', Role::WS_ROLE);
        $query->setParameter('user', $user);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findOneBaseWorkspaceRole($roleType, Workspace $workspace)
    {
        $dql = "
            SELECT r FROM Claroline\CoreBundle\Entity\Role r
            WHERE r.name = 'ROLE_WS_{$roleType}_{$workspace->getGuid()}'
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getOneOrNullResult();
    }
}
