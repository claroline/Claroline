<?php

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;

class UserRepository extends EntityRepository
{
    const PLATEFORM_ROLE = 1;
    const WORKSPACE_ROLE = 2;
    const ALL_ROLES = 2;

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

    public function getUsersOfWorkspace(AbstractWorkspace $workspace, $role = null)
    {
        $dql = "
            SELECT u FROM Claroline\CoreBundle\Entity\User u
            JOIN u.workspaceRoles wr JOIN wr.workspace w WHERE w.id = :id
        ";

        if ($role != null) {
            $dql.= "AND wr.id = :roleId";
        }

        $query = $this->_em->createQuery($dql);
        $query->setParameter('id', $workspace->getId());
        
        if ($role != null){
            $query->setParameter('roleId', $role->getId());
        }

        return $query->getResult();
    }

    //doctrine doesn't have any DQL LIMIT clause.
    public function getLazyUnregisteredUsersOfWorkspace(AbstractWorkspace $workspace, $offset, $limit)
    {
        $dql = "
            SELECT u FROM Claroline\CoreBundle\Entity\User u
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

    public function getUnregisteredUsersOfWorkspaceFromGenericSearch($search, AbstractWorkspace $workspace, $offset, $limit)
    {
        $search = strtoupper($search);

        $dql = "
            SELECT u FROM Claroline\CoreBundle\Entity\User u
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

    public function findPaginatedUsersOfGroup($groupId, $offset, $limit)
    {
        $dql = "
            SELECT u from Claroline\CoreBundle\Entity\User u
            JOIN u.groups g WHERE g.id = :groupId";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('groupId', $groupId);
        $query->setFirstResult($offset);
        $query->setMaxResults($limit);

        return $query->getResult();
    }

    public function findPaginatedUsersOfWorkspace($workspaceId, $offset, $limit)
    {
        $dql = "
            SELECT wr, u from Claroline\CoreBundle\Entity\User u
            JOIN u.workspaceRoles wr JOIN wr.workspace w WHERE w.id = :workspaceId";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspaceId', $workspaceId);
        $query->setFirstResult($offset);
        $query->setMaxResults($limit);

        return $query->getResult();
    }

    public function searchPaginatedUsersOfWorkspace($workspaceId, $search, $offset, $limit)
    {
        $dql = "
            SELECT u, wrol FROM Claroline\CoreBundle\Entity\User u
            JOIN u.workspaceRoles wrol
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
}