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

use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\ORM\EntityRepository;

class RoleRepository extends EntityRepository
{
    /**
     * Returns the collaborator role of a workspace.
     */
    public function findCollaboratorRole(Workspace $workspace): ?Role
    {
        return $this->findBaseWorkspaceRole('COLLABORATOR', $workspace);
    }

    /**
     * Returns the manager role of a workspace.
     */
    public function findManagerRole(Workspace $workspace): ?Role
    {
        return $this->findBaseWorkspaceRole('MANAGER', $workspace);
    }

    /**
     * Returns the platform roles of a user.
     * NB. This method is called in the UserSerializer. We bypass ORM for performances.
     */
    public function loadByUser(User $user): array
    {
        return $this->getEntityManager()
            ->createQuery('
                SELECT r.uuid AS id, r.type, r.name, r.translationKey
                FROM Claroline\CoreBundle\Entity\Role AS r
                WHERE EXISTS (
                    SELECT u.id
                    FROM Claroline\CoreBundle\Entity\User AS u
                    LEFT JOIN u.roles AS r2
                    LEFT JOIN u.groups AS g
                    LEFT JOIN g.roles AS gr
                    WHERE u.id = :userId
                      AND (r2.id = r.id OR gr.id = r.id)
                )
            ')
            ->setParameter('userId', $user->getId())
            ->getArrayResult();
    }

    /**
     * Returns the platform roles of a group.
     * NB. This method is called in the GroupSerializer. We bypass ORM for performances.
     */
    public function loadByGroup(Group $group): array
    {
        return $this->getEntityManager()
            ->createQuery('
                SELECT r.uuid AS id, r.type, r.name, r.translationKey
                FROM Claroline\CoreBundle\Entity\Role AS r
                WHERE EXISTS (
                    SELECT g.id
                    FROM Claroline\CoreBundle\Entity\Group AS g
                    LEFT JOIN g.roles AS r2
                    WHERE g.id = :groupId
                      AND r2.id = r.id
                )
                LEFT JOIN r.groups AS g
                WHERE g.id = :groupId
            ')
            ->setParameter('groupId', $group->getId())
            ->getArrayResult();
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

    private function findBaseWorkspaceRole(string $roleType, Workspace $workspace): ?Role
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
