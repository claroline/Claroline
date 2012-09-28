<?php

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;

class UserRepository extends EntityRepository
{
    const PLATEFORM_ROLE = 1;
    const WORKSPACE_ROLE = 2;
    const ALL_ROLES = 3;

    //todo prepared statement here
    public function getUsersByUsernameList(array $usernames)
    {
        $nameList = array_map(
            function($name) {
                return "'{$name}'";
            }, $usernames
        );
        $nameList = implode(', ', $nameList);
        $dql = "
            SELECT u FROM Claroline\CoreBundle\Entity\User u
            WHERE u.username IN ({$nameList})
            ORDER BY u.username
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function getUsersOfWorkspace(AbstractWorkspace $workspace, $role = null, $areGroupsIncluded = false)
    {
        $dql = "
            SELECT DISTINCT u FROM Claroline\CoreBundle\Entity\User u
            JOIN u.workspaceRoles wr
            JOIN wr.workspace w
            WHERE w.id = {$workspace->getId()}";

        if ($role != null) {
            $dql .= " AND wr.id = {$role->getId()}";
        }

        $query = $this->_em->createQuery($dql);
        $userResults = $query->getResult();

        if ($areGroupsIncluded) {
            $dql = "
                SELECT DISTINCT u FROM Claroline\CoreBundle\Entity\User u
                JOIN u.groups g
                JOIN g.workspaceRoles wr
                JOIN wr.workspace w WHERE w.id = {$workspace->getId()}";

            if ($role != null) {
                $dql.= " AND wr.id = {$role->getId()}";
            }

            $query = $this->_em->createQuery($dql);
            $groupResults = $query->getResult();
        }

        if (isset($groupResults)) {
            return array_merge($userResults, $groupResults);
        } else {
            return $userResults;
        }
    }

    public function searchUnregisteredUsersOfWorkspace($search, AbstractWorkspace $workspace, $offset, $limit)
    {
        $search = strtoupper($search);

        $dql = "
            SELECT u, ws, wrs FROM Claroline\CoreBundle\Entity\User u
            JOIN u.personnalWorkspace ws
            JOIN u.workspaceRoles wrs
            WHERE UPPER(u.lastName) LIKE :search
            AND u NOT IN
            (
            SELECT us FROM Claroline\CoreBundle\Entity\User us
            JOIN us.workspaceRoles wr
            JOIN wr.workspace w
            WHERE w.id = :id
            )
            OR UPPER(u.firstName) LIKE :search
            AND u NOT IN
            (
            SELECT use FROM Claroline\CoreBundle\Entity\User use
            JOIN use.workspaceRoles wro
            JOIN wro.workspace wo
            WHERE wo.id = :id
            )
            OR UPPER(u.username) LIKE :search
            AND u NOT IN
            (
            SELECT user FROM Claroline\CoreBundle\Entity\User user
            JOIN user.workspaceRoles wrol
            JOIN wrol.workspace wol
            WHERE wol.id = :id
            )
        ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('id', $workspace->getId())
            ->setParameter('search', "%{$search}%")
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $query->getResult();
    }

    public function unregisteredUsersOfWorkspace(AbstractWorkspace $workspace, $offset, $limit)
    {
        $dql = "
            SELECT u, ws, wrs FROM Claroline\CoreBundle\Entity\User u
            JOIN u.personnalWorkspace ws
            JOIN u.workspaceRoles wrs
            WHERE u NOT IN
            (
                SELECT us FROM Claroline\CoreBundle\Entity\User us
                JOIN us.workspaceRoles wr
                JOIN wr.workspace w
                WHERE w.id = :id
            )
        ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('id', $workspace->getId());
        $query->setMaxResults($limit);
        $query->setFirstResult($offset);

        return $query->getResult();
    }

    /**
     * Current logged user will see all his roles
     */
    public function findPaginatedUsers($offset, $limit, $modeRole)
    {
        switch($modeRole){
            case self::PLATEFORM_ROLE:
                $dql = 'SELECT u, r from Claroline\CoreBundle\Entity\User u JOIN u.roles r
                    WHERE r NOT INSTANCE OF Claroline\CoreBundle\Entity\WorkspaceRole';
                break;
        }
        $query = $this->_em->createQuery($dql)
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $query->getResult();
    }

    public function searchPaginatedUsers($search, $offset, $limit)
    {
        $dql = "
            SELECT u FROM Claroline\CoreBundle\Entity\User u
            WHERE UPPER(u.lastName) LIKE :search
            OR UPPER(u.firstName) LIKE :search
            OR UPPER(u.username) LIKE :search";

        $query = $this->_em->createQuery($dql)
              ->setParameter('search', "%{$search}%")
              ->setFirstResult($offset)
              ->setMaxResults($limit);

        return $query->getResult();
    }

    public function findPaginatedUsersOfGroup($groupId, $offset, $limit)
    {
        $dql = "
            SELECT DISTINCT u, g, pw, wr from Claroline\CoreBundle\Entity\User u
            JOIN u.groups g
            JOIN u.personnalWorkspace pw
            JOIN u.workspaceRoles wr
            WHERE g.id = :groupId ORDER BY u.id";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('groupId', $groupId);
        $query->setFirstResult($offset);
        $query->setMaxResults($limit);

        return $query->getResult();
    }

    public function searchPaginatedUserOfGroups($search, $groupId, $offset, $limit)
    {
        $dql = "
            SELECT DISTINCT u, g, pw, wr from Claroline\CoreBundle\Entity\User u
            JOIN u.groups g
            JOIN u.personnalWorkspace pw
            JOIN u.workspaceRoles wr
            WHERE g.id = :groupId
            AND (UPPER(u.username) LIKE :search
            OR UPPER(u.lastName) LIKE :search
            OR UPPER(u.firstName) LIKE :search)
        ORDER BY u.id";

        $query = $this->_em->createQuery($dql)
            ->setParameter('search', "%{$search}%")
            ->setParameter('groupId', $groupId)
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $query->getResult();
    }

    public function registeredUsersOfWorkspace($workspaceId, $offset, $limit)
    {
        $dql = "
            SELECT wr, u, ws from Claroline\CoreBundle\Entity\User u
            JOIN u.workspaceRoles wr
            JOIN wr.workspace w
            JOIN u.personnalWorkspace ws
            WHERE w.id = :workspaceId";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspaceId', $workspaceId);
        $query->setFirstResult($offset);
        $query->setMaxResults($limit);

        return $query->getResult();
    }

    public function searchRegisteredUsersOfWorkspace($workspaceId, $search, $offset, $limit)
    {
        $dql = "
            SELECT u, wrol, ws FROM Claroline\CoreBundle\Entity\User u
            JOIN u.workspaceRoles wrol
            JOIN u.personnalWorkspace ws
            JOIN wrol.workspace wol
            WHERE wol.id = :workspaceId AND u IN (SELECT us FROM Claroline\CoreBundle\Entity\User us WHERE
            UPPER(us.lastName) LIKE :search
            OR UPPER(us.firstName) LIKE :search
            OR UPPER(us.username) LIKE :search
            )
        ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspaceId', $workspaceId)
              ->setParameter('search', "%{$search}%")
              ->setFirstResult($offset)
              ->setMaxResults($limit);

        return $query->getResult();
    }

    public function getRoleOfWorkspace($userId, $workspaceId)
    {
        $dql = "
            SELECT wr FROM Claroline\CoreBundle\Entity\WorkspaceRole wr
            JOIN wr.workspace ws
            JOIN wr.users u
            WHERE ws.id = :workspaceId
            AND u.id = :userId
       ";

       $query = $this->_em->createQuery($dql);
       $query->setParameter('workspaceId', $workspaceId);
       $query->setParameter('userId', $userId);

       return $query->getResult();
    }

    public function findUnregisteredUsersFromGroup($groupId, $offset, $limit)
    {
        $dql = "
            SELECT u, ws, wrs FROM Claroline\CoreBundle\Entity\User u
            JOIN u.personnalWorkspace ws
            JOIN u.workspaceRoles wrs
            WHERE u NOT IN (
                SELECT us FROM Claroline\CoreBundle\Entity\User us
                JOIN us.groups gs
                WHERE gs.id = :groupId
            ) ORDER BY u.id
        ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('groupId', $groupId)
              ->setFirstResult($offset)
              ->setMaxResults($limit);

        return $query->getResult();
    }

    public function searchUnregisteredUsersFromGroup($groupId, $search, $offset, $limit)
    {
        $search = strtoupper($search);

        $dql = "
            SELECT DISTINCT u, ws, wrs FROM Claroline\CoreBundle\Entity\User u
            JOIN u.personnalWorkspace ws
            JOIN u.workspaceRoles wrs
            WHERE UPPER(u.lastName) LIKE :search
            AND u NOT IN (
                SELECT us FROM Claroline\CoreBundle\Entity\User us
                JOIN us.groups gr
                WHERE gr.id = :groupId
            )
            OR UPPER(u.firstName) LIKE :search
            AND u NOT IN (
                SELECT use FROM Claroline\CoreBundle\Entity\User use
                JOIN use.groups gro
                WHERE gro.id = :groupId
            )
            OR UPPER(u.lastName) LIKE :search
            AND u NOT IN (
                SELECT user FROM Claroline\CoreBundle\Entity\User user
                JOIN user.groups grou
                WHERE grou.id = :groupId
            )";

       $query = $this->_em->createQuery($dql);
        $query->setParameter('groupId', $groupId)
              ->setParameter('search', "%{$search}%")
              ->setFirstResult($offset)
              ->setMaxResults($limit);

        return $query->getResult();
    }
}