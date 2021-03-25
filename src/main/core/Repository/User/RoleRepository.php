<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Repository\User;

use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\ToolMaskDecoder;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\ORM\EntityRepository;

class RoleRepository extends EntityRepository
{
    /**
     * Returns the collaborator role of a workspace.
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
     * @return Role
     */
    public function findManagerRole(Workspace $workspace)
    {
        return $this->findBaseWorkspaceRole('MANAGER', $workspace);
    }

    /**
     * Returns the platform roles of a user.
     *
     * @return Role[]
     */
    public function findPlatformRoles(User $user)
    {
        $dql = "
            SELECT r FROM Claroline\\CoreBundle\\Entity\\Role r
            JOIN r.users u
            WHERE u.id = {$user->getId()} AND r.type = ".Role::PLATFORM_ROLE;
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    /**
     * Returns all platform roles.
     *
     * @return Role[]
     */
    public function findAllPlatformRoles()
    {
        return $this
            ->createQueryBuilder('role')
            ->andWhere('role.type = :roleType')
            ->setParameter('roleType', Role::PLATFORM_ROLE)
            ->andWhere('role.name NOT LIKE :anonymous')
            ->setParameter('anonymous', 'ROLE_ANONYMOUS')
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns the roles of a user in a workspace.
     *
     * @param User      $user      The subject of the role
     * @param Workspace $workspace The workspace the role should be bound to
     *
     * @return Role[]
     */
    public function findWorkspaceRolesForUser(User $user, Workspace $workspace)
    {
        $dql = "
            SELECT r FROM Claroline\\CoreBundle\\Entity\\Role r
            JOIN r.workspace ws
            JOIN r.users user
            WHERE ws.uuid = '{$workspace->getUuid()}'
            AND r.name != 'ROLE_ADMIN'
            AND user.id = {$user->getId()}
        ";

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    private function findBaseWorkspaceRole($roleType, Workspace $workspace)
    {
        $dql = "
            SELECT r FROM Claroline\\CoreBundle\\Entity\\Role r
            WHERE r.name LIKE '%ROLE_WS_{$roleType}_{$workspace->getUuid()}%'
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getOneOrNullResult();
    }

    public function findAll()
    {
        $dql = '
            SELECT r, w
            FROM Claroline\CoreBundle\Entity\Role r
            LEFT JOIN r.workspace w
        ';

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
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
     * Returns user-type role of an user.
     */
    public function findUserRoleByUsername(string $username): ?Role
    {
        return $this->_em
            ->createQuery('
                SELECT r
                FROM Claroline\CoreBundle\Entity\Role r
                WHERE r.type = :type
                AND r.name = :name
                AND r.translationKey = :key
            ')
            ->setParameter('type', Role::USER_ROLE)
            ->setParameter('name', 'ROLE_USER_'.strtoupper($username))
            ->setParameter('key', $username)
            ->getOneOrNullResult();
    }

    public function findWorkspaceRoleWithToolAccess(Workspace $workspace)
    {
        $dql = '
            SELECT r
            FROM Claroline\CoreBundle\Entity\Role r
            WHERE
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
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);
        $query->setParameter('managerRoleName', 'ROLE_WS_MANAGER_'.$workspace->getUuid());
        $query->setParameter('openValue', ToolMaskDecoder::$defaultValues['open']);

        return $query->getResult();
    }
}
