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

use Claroline\CommunityBundle\Entity\Team;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\ORM\EntityRepository;

class TeamRepository extends EntityRepository
{
    public function countUsers(Team $team): int
    {
        return (int) $this->getEntityManager()
            ->createQuery('
                SELECT COUNT(u) 
                FROM Claroline\CommunityBundle\Entity\Team AS t
                LEFT JOIN t.role r
                LEFT JOIN r.users AS u
                WHERE t = :team
            ')
            ->setParameters([
                'team' => $team,
            ])
            ->getSingleScalarResult();
    }

    public function findByRole(string $roleName)
    {
        $dql = "
            SELECT t
            FROM Claroline\CommunityBundle\Entity\Team t
            LEFT JOIN t.role r
            LEFT JOIN t.managerRole mr
            WHERE r.name = :role 
               OR mr.name = :role
        ";
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('role', $roleName);

        return $query->getResult();
    }

    public function findTeamsByUserAndWorkspace(User $user, Workspace $workspace)
    {
        $dql = '
            SELECT t
            FROM Claroline\CommunityBundle\Entity\Team t
            JOIN t.role r
            JOIN r.users u
            WHERE t.workspace = :workspace
            AND u = :user
        ';
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('workspace', $workspace);
        $query->setParameter('user', $user);

        return $query->getResult();
    }

    /**
     * Gets the list of Workspace users which are not in a team excluding the managers of the workspace.
     *
     * @return array
     */
    public function findUsersWithNoTeamByWorkspace(Workspace $workspace, array $teams)
    {
        $dql = "
            SELECT DISTINCT u
            FROM Claroline\CoreBundle\Entity\User u
            WHERE u.isRemoved = false
            AND u.disabled = false
            AND (
                u IN (
                    SELECT DISTINCT u1
                    FROM Claroline\CoreBundle\Entity\User u1
                    JOIN u1.roles r1 WITH r1 IN (
                        SELECT r12
                        FROM Claroline\CoreBundle\Entity\Role r12
                        WHERE r12.workspace = :workspace
                    )
                    WHERE u1.isRemoved = false
                    AND u1.disabled = false
                )
                OR u IN (
                    SELECT DISTINCT u2
                    FROM Claroline\CoreBundle\Entity\User u2
                    JOIN u2.groups g
                    JOIN g.roles r2 WITH r2 IN (
                        SELECT r22
                        FROM Claroline\CoreBundle\Entity\Role r22
                        WHERE r22.workspace = :workspace
                    )
                    WHERE u2.isRemoved = false
                    AND u2.disabled = false
                )
            )
            AND u NOT IN (
                SELECT DISTINCT u3
                FROM Claroline\CoreBundle\Entity\User u3
                WHERE EXISTS (
                    SELECT t
                    FROM Claroline\CommunityBundle\Entity\Team t
                    JOIN t.role tr
                    JOIN tr.users u4
                    WHERE t IN (:teams)
                    AND u4 = u3
                )
            )
            AND u NOT IN (
                SELECT DISTINCT u5
                FROM Claroline\CoreBundle\Entity\User u5
                JOIN u5.roles r5 WITH r5 IN (
                    SELECT r52
                    FROM Claroline\CoreBundle\Entity\Role r52
                    WHERE r52.workspace = :workspace
                    AND r52.name = :workspaceManagerName
                )
                WHERE u5.isRemoved = false
                AND u5.disabled = false
            )
            AND u NOT IN (
                SELECT DISTINCT u6
                FROM Claroline\CoreBundle\Entity\User u6
                JOIN u6.groups g2
                JOIN g2.roles r6 WITH r6 IN (
                    SELECT r62
                    FROM Claroline\CoreBundle\Entity\Role r62
                    WHERE r62.workspace = :workspace
                    AND r62.name = :workspaceManagerName
                )
                WHERE u6.isRemoved = false
                AND u6.disabled = false
            )
        ";
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('workspace', $workspace);
        $query->setParameter('workspaceManagerName', 'ROLE_WS_MANAGER_'.$workspace->getUuid());
        $query->setParameter('teams', $teams);

        return $query->getResult();
    }
}
