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
    )
    {
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

    public function findTeamsByWorkspaceAndName(
        Workspace $workspace,
        $teamName,
        $executeQuery = true
    )
    {
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
    )
    {
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

    public function findNbTeamsByUserAndWorkspace(
        User $user,
        Workspace $workspace,
        $executeQuery = true
    )
    {
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
    )
    {
        $dql = "
            SELECT DISTINCT u
            FROM Claroline\CoreBundle\Entity\User u
            WHERE u.isEnabled = true
            AND (
                u IN (
                    SELECT DISTINCT u1
                    FROM Claroline\CoreBundle\Entity\User u1
                    JOIN u1.roles r1 WITH r1 IN (
                        SELECT r12
                        FROM Claroline\CoreBundle\Entity\Role r12
                        WHERE r12.workspace = :workspace
                        AND r12 != :role
                    )
                    WHERE u1.isEnabled = true
                )
                OR u IN (
                    SELECT DISTINCT u2
                    FROM Claroline\CoreBundle\Entity\User u2
                    JOIN u2.groups g
                    JOIN g.roles r2 WITH r2 IN (
                        SELECT r22
                        FROM Claroline\CoreBundle\Entity\Role r22
                        WHERE r22.workspace = :workspace
                        AND r22 != :role
                    )
                    WHERE u2.isEnabled = true
                )
            )
            ORDER BY u.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $team->getWorkspace());
        $query->setParameter('role', $team->getRole());

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findSearchedUnregisteredUsersByTeam(
        Team $team,
        $search = '',
        $orderedBy = 'firstName',
        $order = 'ASC',
        $executeQuery = true
    )
    {
        $dql = "
            SELECT DISTINCT u
            FROM Claroline\CoreBundle\Entity\User u
            WHERE u.isEnabled = true
            AND (
                u IN (
                    SELECT DISTINCT u1
                    FROM Claroline\CoreBundle\Entity\User u1
                    JOIN u1.roles r1 WITH r1 IN (
                        SELECT r12
                        FROM Claroline\CoreBundle\Entity\Role r12
                        WHERE r12.workspace = :workspace
                        AND r12 != :role
                    )
                    WHERE u1.isEnabled = true
                )
                OR u IN (
                    SELECT DISTINCT u2
                    FROM Claroline\CoreBundle\Entity\User u2
                    JOIN u2.groups g
                    JOIN g.roles r2 WITH r2 IN (
                        SELECT r22
                        FROM Claroline\CoreBundle\Entity\Role r22
                        WHERE r22.workspace = :workspace
                        AND r22 != :role
                    )
                    WHERE u2.isEnabled = true
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
        $query->setParameter('workspace', $team->getWorkspace());
        $query->setParameter('role', $team->getRole());
        $upperSearch = strtoupper($search);
        $query->setParameter('search', "%{$upperSearch}%");

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findWorkspaceUsers(
        Workspace $workspace,
        $orderedBy = 'firstName',
        $order = 'ASC',
        $executeQuery = true
    )
    {
        $dql = "
            SELECT DISTINCT u
            FROM Claroline\CoreBundle\Entity\User u
            WHERE u.isEnabled = true
            AND (
                u IN (
                    SELECT DISTINCT u1
                    FROM Claroline\CoreBundle\Entity\User u1
                    JOIN u1.roles r1 WITH r1 IN (
                        SELECT r12
                        FROM Claroline\CoreBundle\Entity\Role r12
                        WHERE r12.workspace = :workspace
                    )
                    WHERE u1.isEnabled = true
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
                    WHERE u2.isEnabled = true
                )
            )
            ORDER BY u.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findSearchedWorkspaceUsers(
        Workspace $workspace,
        $search = '',
        $orderedBy = 'firstName',
        $order = 'ASC',
        $executeQuery = true
    )
    {
        $dql = "
            SELECT DISTINCT u
            FROM Claroline\CoreBundle\Entity\User u
            WHERE u.isEnabled = true
            AND (
                u IN (
                    SELECT DISTINCT u1
                    FROM Claroline\CoreBundle\Entity\User u1
                    JOIN u1.roles r1 WITH r1 IN (
                        SELECT r12
                        FROM Claroline\CoreBundle\Entity\Role r12
                        WHERE r12.workspace = :workspace
                    )
                    WHERE u1.isEnabled = true
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
                    WHERE u2.isEnabled = true
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
}
