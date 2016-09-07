<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\TeamBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\TeamBundle\Entity\Team;
use Doctrine\ORM\EntityRepository;

class TeamRepository extends EntityRepository
{
    public function findTeamsByWorkspace(
        Workspace $workspace,
        $orderedBy = 'name',
        $order = 'ASC',
        $executeQuery = true
    ) {
        $dql = "
            SELECT t
            FROM Claroline\TeamBundle\Entity\Team t
            WHERE t.workspace = :workspace
            ORDER BY t.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findTeamsByUser(
        User $user,
        $orderedBy = 'name',
        $order = 'ASC',
        $executeQuery = true
    ) {
        $dql = "
            SELECT t
            FROM Claroline\TeamBundle\Entity\Team t
            JOIN t.users tu
            WHERE tu = :user
            ORDER BY t.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findTeamsByWorkspaceAndName(
        Workspace $workspace,
        $teamName,
        $executeQuery = true
    ) {
        $dql = '
            SELECT t
            FROM Claroline\TeamBundle\Entity\Team t
            WHERE t.workspace = :workspace
            AND t.name = :teamName
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);
        $query->setParameter('teamName', $teamName);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findTeamsWithUsersByWorkspace(
        Workspace $workspace,
        $executeQuery = true
    ) {
        $dql = '
            SELECT t AS team, COUNT(u) AS nb_users
            FROM Claroline\TeamBundle\Entity\Team t
            JOIN t.users u
            WHERE t.workspace = :workspace
            GROUP BY t
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findTeamsByUserAndWorkspace(
        User $user,
        Workspace $workspace,
        $executeQuery = true
    ) {
        $dql = '
            SELECT t
            FROM Claroline\TeamBundle\Entity\Team t
            JOIN t.users u
            WHERE t.workspace = :workspace
            AND u = :user
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);
        $query->setParameter('user', $user);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findUnregisteredUsersByTeam(
        Team $team,
        $orderedBy = 'firstName',
        $order = 'ASC',
        $executeQuery = true
    ) {
        $dql = "
            SELECT DISTINCT u
            FROM Claroline\CoreBundle\Entity\User u
            WHERE u.isRemoved = false
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
                )
            )
            AND u NOT IN (
                SELECT DISTINCT u3
                FROM Claroline\CoreBundle\Entity\User u3
                WHERE EXISTS (
                    SELECT t
                    FROM Claroline\TeamBundle\Entity\Team t
                    JOIN t.users u4
                    WHERE t = :team
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
            )
            ORDER BY u.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $workspace = $team->getWorkspace();
        $query->setParameter('workspace', $workspace);
        $query->setParameter('team', $team);
        $query->setParameter(
            'workspaceManagerName',
            'ROLE_WS_MANAGER_'.$workspace->getGuid()
        );

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findSearchedUnregisteredUsersByTeam(
        Team $team,
        $search = '',
        $orderedBy = 'firstName',
        $order = 'ASC',
        $executeQuery = true
    ) {
        $dql = "
            SELECT DISTINCT u
            FROM Claroline\CoreBundle\Entity\User u
            WHERE u.isRemoved = false
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
                )
            )
            AND u NOT IN (
                SELECT DISTINCT u3
                FROM Claroline\CoreBundle\Entity\User u3
                WHERE EXISTS (
                    SELECT t
                    FROM Claroline\TeamBundle\Entity\Team t
                    JOIN t.users u4
                    WHERE t = :team
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
            )
            AND
            (
                UPPER(u.firstName) LIKE :search
                OR UPPER(u.lastName) LIKE :search
                OR UPPER(u.username) LIKE :search
            )
            ORDER BY u.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $workspace = $team->getWorkspace();
        $query->setParameter('workspace', $workspace);
        $query->setParameter('team', $team);
        $query->setParameter(
            'workspaceManagerName',
            'ROLE_WS_MANAGER_'.$workspace->getGuid()
        );
        $upperSearch = strtoupper($search);
        $query->setParameter('search', "%{$upperSearch}%");

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findWorkspaceUsers(
        Workspace $workspace,
        $orderedBy = 'firstName',
        $order = 'ASC',
        $executeQuery = true
    ) {
        $dql = "
            SELECT DISTINCT u
            FROM Claroline\CoreBundle\Entity\User u
            WHERE u.isRemoved = false
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
            )
            ORDER BY u.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);
        $query->setParameter(
            'workspaceManagerName',
            'ROLE_WS_MANAGER_'.$workspace->getGuid()
        );

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findSearchedWorkspaceUsers(
        Workspace $workspace,
        $search = '',
        $orderedBy = 'firstName',
        $order = 'ASC',
        $executeQuery = true
    ) {
        $dql = "
            SELECT DISTINCT u
            FROM Claroline\CoreBundle\Entity\User u
            WHERE u.isRemoved = false
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
            )
            AND
            (
                UPPER(u.firstName) LIKE :search
                OR UPPER(u.lastName) LIKE :search
                OR UPPER(u.username) LIKE :search
            )
            ORDER BY u.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);
        $query->setParameter(
            'workspaceManagerName',
            'ROLE_WS_MANAGER_'.$workspace->getGuid()
        );
        $upperSearch = strtoupper($search);
        $query->setParameter('search', "%{$upperSearch}%");

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findWorkspaceUsersWithManagers(
        Workspace $workspace,
        $orderedBy = 'firstName',
        $order = 'ASC',
        $executeQuery = true
    ) {
        $dql = "
            SELECT DISTINCT u
            FROM Claroline\CoreBundle\Entity\User u
            WHERE u.isRemoved = false
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
                )
            )
            ORDER BY u.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findSearchedWorkspaceUsersWithManagers(
        Workspace $workspace,
        $search = '',
        $orderedBy = 'firstName',
        $order = 'ASC',
        $executeQuery = true
    ) {
        $dql = "
            SELECT DISTINCT u
            FROM Claroline\CoreBundle\Entity\User u
            WHERE u.isRemoved = false
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
                )
            )
            AND
            (
                UPPER(u.firstName) LIKE :search
                OR UPPER(u.lastName) LIKE :search
                OR UPPER(u.username) LIKE :search
            )
            ORDER BY u.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);
        $upperSearch = strtoupper($search);
        $query->setParameter('search', "%{$upperSearch}%");

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findNbTeamsByUsers(
        Workspace $workspace,
        array $users,
        $executeQuery = true
    ) {
        $dql = '
            SELECT COUNT(t.id) AS nb_teams, u.id AS user_id
            FROM Claroline\TeamBundle\Entity\Team t
            JOIN t.users u WITH u IN (:users)
            WHERE t.workspace = :workspace
            GROUP BY u.id
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);
        $query->setParameter('users', $users);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findUsersWithNoTeamByWorkspace(
        Workspace $workspace,
        array $teams,
        $executeQuery = true
    ) {
        $dql = "
            SELECT DISTINCT u
            FROM Claroline\CoreBundle\Entity\User u
            WHERE u.isRemoved = false
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
                )
            )
            AND u NOT IN (
                SELECT DISTINCT u3
                FROM Claroline\CoreBundle\Entity\User u3
                WHERE EXISTS (
                    SELECT t
                    FROM Claroline\TeamBundle\Entity\Team t
                    JOIN t.users u4
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
            )
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);
        $query->setParameter(
            'workspaceManagerName',
            'ROLE_WS_MANAGER_'.$workspace->getGuid()
        );
        $query->setParameter('teams', $teams);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findTeamsWithExclusionsByWorkspace(
        Workspace $workspace,
        array $excludedTeams,
        $orderedBy = 'name',
        $order = 'ASC',
        $executeQuery = true
    ) {
        $dql = "
            SELECT t
            FROM Claroline\TeamBundle\Entity\Team t
            WHERE t.workspace = :workspace
            AND t NOT IN (:excludedTeams)
            ORDER BY t.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);
        $query->setParameter('excludedTeams', $excludedTeams);

        return $executeQuery ? $query->getResult() : $query;
    }
}
