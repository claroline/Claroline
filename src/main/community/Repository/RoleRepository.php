<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CommunityBundle\Repository;

use Claroline\CoreBundle\Entity\Role;
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
        return $this->getEntityManager()
            ->createQuery('
                SELECT r 
                FROM Claroline\CoreBundle\Entity\Role r
                JOIN r.users u
                WHERE u.id = :userId 
                  AND r.type = :type
            ')
            ->setParameter('userId', $user->getId())
            ->setParameter('type', Role::PLATFORM_ROLE)
            ->getResult();
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
     * Returns user-type role of a user.
     */
    public function findUserRoleByUsername(string $username): ?Role
    {
        return $this->getEntityManager()
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

    private function findBaseWorkspaceRole(string $roleType, Workspace $workspace)
    {
        return $this->getEntityManager()
            ->createQuery('
                SELECT r FROM Claroline\CoreBundle\Entity\Role r
                WHERE r.name LIKE :role_pattern'
            )
            ->setParameter('role_pattern', "ROLE_WS_{$roleType}_{$workspace->getUuid()}%")
            ->getOneOrNullResult();
    }
}
