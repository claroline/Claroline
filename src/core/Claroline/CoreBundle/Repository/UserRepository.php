<?php

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Doctrine\ORM\NoResultException;

class UserRepository extends EntityRepository implements UserProviderInterface
{
    const PLATEFORM_ROLE = 1;
    const WORKSPACE_ROLE = 2;
    const ALL_ROLES = 3;

    /*
     * UserProviderInterface method
     */
    public function loadUserByUsername($username)
    {
        $dql = "SELECT u FROM Claroline\CoreBundle\Entity\User u
            WHERE u.username LIKE :username"
            ;

        $query = $this->_em->createQuery($dql);
        $query->setParameter('username', $username);

        try {

            $user = $query->getSingleResult();
            // The Query::getSingleResult() method throws an exception
            // if there is no record matching the criteria.

        } catch (NoResultException $e) {
            throw new UsernameNotFoundException(
                sprintf('Unable to find an active admin AcmeUserBundle:User object identified by "%s".', $username),
                null,
                0,
                $e
            );
        }

        return $user;
    }

    /*
     * UserProviderInterface method
     */
    public function refreshUser(UserInterface $user)
    {
        $class = get_class($user);

        if (!$this->supportsClass($class)) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $class));
        }

        $dql = "SELECT u, groups, group_roles, roles, ws, wrights FROM Claroline\CoreBundle\Entity\User u
            LEFT JOIN u.groups groups
            LEFT JOIN groups.roles group_roles
            LEFT JOIN u.roles roles
            LEFT JOIN roles.workspaceRights wrights
            LEFT JOIN wrights.workspace ws
            WHERE u.id = :userId";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('userId', $user->getId());
        $user =  $query->getSingleResult();

        //Deep doctrine sorcery goes here.
        //This will help doctrine to not make 1 request by role.

        $dql = "SELECT r FROM Claroline\CoreBundle\Entity\Role r
            LEFT JOIN r.users users WITH users IN (
                SELECT u from Claroline\CoreBundle\Entity\User u where u.id = {$user->getId()}
            )";
        $query = $this->_em->createQuery($dql);
        $query->getResult();

        return $user;
    }

    /*
     * UserProviderInterface method
     */
    public function supportsClass($class)
    {
        return $this->getEntityName() === $class || is_subclass_of($class, $this->getEntityName());
    }

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
            LEFT JOIN u.roles wr WITH wr IN (
                SELECT pr from Claroline\CoreBundle\Entity\Role pr WHERE pr.roleType = ".Role::WS_ROLE."
            )
            LEFT JOIN wr.workspaceRights wrr
            LEFT JOIN wrr.workspace w
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
                JOIN g.roles wr WITH wr IN (
                    SELECT pr from Claroline\CoreBundle\Entity\Role pr WHERE pr.roleType = ".Role::WS_ROLE."
                )
                LEFT JOIN wr.workspaceRights wrr
                LEFT JOIN wrr.workspace w
                WHERE w.id = {$workspace->getId()}";

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
            SELECT u, ws, r FROM Claroline\CoreBundle\Entity\User u
            JOIN u.personalWorkspace ws
            LEFT JOIN u.roles r WITH r IN (
                SELECT pr from Claroline\CoreBundle\Entity\Role pr WHERE pr.roleType = ".Role::WS_ROLE."
            )
            WHERE UPPER(u.lastName) LIKE :search
            AND u NOT IN
            (
            SELECT user_1 FROM Claroline\CoreBundle\Entity\User user_1
            LEFT JOIN user_1.roles role_1
            WITH role_1 IN (
                SELECT pr2 from Claroline\CoreBundle\Entity\Role pr2 WHERE pr2.roleType = ".Role::WS_ROLE."
            )
            JOIN role_1.workspaceRights rights_1
            JOIN rights_1.workspace workspace_1
            WHERE workspace_1.id = :id
            )
            OR UPPER(u.firstName) LIKE :search
            AND u NOT IN
            (
            SELECT user_2 FROM Claroline\CoreBundle\Entity\User user_2
            LEFT JOIN user_2.roles role_2 WITH role_2 IN (
                SELECT pr3 from Claroline\CoreBundle\Entity\Role pr3 WHERE pr3.roleType = ".Role::WS_ROLE."
            )
            JOIN role_2.workspaceRights rights_2
            JOIN rights_2.workspace workspace_2
            WHERE workspace_2.id = :id
            )
            OR UPPER(u.username) LIKE :search
            AND u NOT IN
            (
            SELECT user_3 FROM Claroline\CoreBundle\Entity\User user_3
            LEFT JOIN user_3.roles role_3 WITH role_3 IN (
                SELECT pr4 from Claroline\CoreBundle\Entity\Role pr4 WHERE pr4.roleType = ".Role::WS_ROLE."
            )
            JOIN role_3.workspaceRights rights_3
            JOIN rights_3.workspace workspace_3
            WHERE workspace_3.id = :id
            )
        ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('id', $workspace->getId())
            ->setParameter('search', "%{$search}%")
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        $paginator = new Paginator($query, true);

        return $paginator;
    }

    public function unregisteredUsersOfWorkspace(AbstractWorkspace $workspace, $offset, $limit)
    {
        $dql = "
            SELECT u, ws, r FROM Claroline\CoreBundle\Entity\User u
            LEFT JOIN u.personalWorkspace ws
            LEFT JOIN u.roles r
            WITH r IN (SELECT pr from Claroline\CoreBundle\Entity\Role pr WHERE pr.roleType = ".Role::WS_ROLE.")
            WHERE u NOT IN
            (
                SELECT us FROM Claroline\CoreBundle\Entity\User us
                LEFT JOIN us.roles wr WITH wr IN (
                    SELECT pr2 from Claroline\CoreBundle\Entity\Role pr2 WHERE pr2.roleType = ".Role::WS_ROLE."
                )
                LEFT JOIN wr.workspaceRights rights
                LEFT JOIN rights.workspace w
                WHERE w.id = :id
            )
        ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('id', $workspace->getId());
        $query->setMaxResults($limit);
        $query->setFirstResult($offset);
        $paginator = new Paginator($query, true);

        return $paginator;
    }

    public function users($offset, $limit, $modeRole)
    {
        switch($modeRole){
            case self::PLATEFORM_ROLE:
                $dql = 'SELECT u, r, pws from Claroline\CoreBundle\Entity\User u
                    JOIN u.roles r WITH r IN (
                        SELECT pr from Claroline\CoreBundle\Entity\Role pr WHERE pr.roleType = '.Role::BASE_ROLE.'
                    )
                    JOIN u.personalWorkspace pws';
                break;
        }
        $query = $this->_em->createQuery($dql)
            ->setFirstResult($offset)
            ->setMaxResults($limit);
        $paginator = new Paginator($query, true);

        return $paginator;
    }

    public function searchUsers($search, $offset, $limit)
    {
        $dql = "
            SELECT u, r, pws FROM Claroline\CoreBundle\Entity\User u
            JOIN u.roles r
            JOIN u.personalWorkspace pws
            WHERE UPPER(u.lastName) LIKE :search
            OR UPPER(u.firstName) LIKE :search
            OR UPPER(u.username) LIKE :search";

        $query = $this->_em->createQuery($dql)
              ->setParameter('search', "%{$search}%")
              ->setFirstResult($offset)
              ->setMaxResults($limit);

        $paginator = new Paginator($query, true);

        return $paginator;
    }

    public function usersOfGroup($groupId, $offset = null, $limit = null)
    {
        $dql = "
            SELECT DISTINCT u, g, pw, r from Claroline\CoreBundle\Entity\User u
            JOIN u.groups g
            JOIN u.personalWorkspace pw
            JOIN u.roles r WITH r IN (
                SELECT pr from Claroline\CoreBundle\Entity\Role pr WHERE pr.roleType = ".Role::BASE_ROLE."
            )
            WHERE g.id = :groupId ORDER BY u.id";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('groupId', $groupId);
        $query->setFirstResult($offset);
        $query->setMaxResults($limit);

        $paginator = new Paginator($query, true);

        return $paginator;
    }

    public function searchUsersOfGroup($search, $groupId, $offset, $limit)
    {
        $dql = "
            SELECT DISTINCT u, g, pw, r from Claroline\CoreBundle\Entity\User u
            JOIN u.groups g
            JOIN u.personalWorkspace pw
            JOIN u.roles r WITH r IN (
                SELECT pr from Claroline\CoreBundle\Entity\Role pr WHERE pr.roleType = ".Role::BASE_ROLE."
            )
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

        $paginator = new Paginator($query, true);

        return $paginator;
    }

    public function registeredUsersOfWorkspace($workspaceId, $offset, $limit)
    {
        $dql = "
            SELECT wr, u, ws from Claroline\CoreBundle\Entity\User u
            JOIN u.roles wr WITH wr IN (
                SELECT pr from Claroline\CoreBundle\Entity\Role pr WHERE pr.roleType = ".Role::WS_ROLE."
            )
            LEFT JOIN wr.workspaceRights rights
            JOIN rights.workspace w
            JOIN u.personalWorkspace ws
            WHERE w.id = :workspaceId";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspaceId', $workspaceId);
        $query->setFirstResult($offset);
        $query->setMaxResults($limit);

        $paginator = new Paginator($query, true);

        return $paginator;
    }

    public function searchRegisteredUsersOfWorkspace($workspaceId, $search, $offset, $limit)
    {
        $dql = "
            SELECT u, r, ws FROM Claroline\CoreBundle\Entity\User u
            JOIN u.roles r WITH r IN (
                SELECT pr from Claroline\CoreBundle\Entity\Role pr WHERE pr.roleType = ".Role::WS_ROLE."
            )
            LEFT JOIN r.workspaceRights rights
            JOIN rights.workspace wol
            JOIN u.personalWorkspace ws
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

        $paginator = new Paginator($query, true);

        return $paginator;
    }

    public function unregisteredUsersOfGroup($groupId, $offset, $limit)
    {
        $dql = "
            SELECT DISTINCT u, ws, r FROM Claroline\CoreBundle\Entity\User u
            JOIN u.personalWorkspace ws
            JOIN u.roles r WITH r IN (
                SELECT pr from Claroline\CoreBundle\Entity\Role pr WHERE pr.roleType = ".Role::BASE_ROLE."
            )
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

        $paginator = new Paginator($query, true);

        return $paginator;
    }

    public function searchUnregisteredUsersOfGroup($groupId, $search, $offset, $limit)
    {
        $search = strtoupper($search);

        $dql = "
            SELECT DISTINCT u, ws, r FROM Claroline\CoreBundle\Entity\User u
            JOIN u.personalWorkspace ws
            JOIN u.roles r WITH r IN (
                SELECT pr from Claroline\CoreBundle\Entity\Role pr WHERE pr.roleType = ".Role::BASE_ROLE."
            )
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

        $paginator = new Paginator($query, true);

        return $paginator;
    }

    /**
     * Checks if a user is registered in a workspace.
     *
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @return boolean
     */
    public function isRegisteredInWorkspace(AbstractWorkspace $workspace, User $user)
    {
        $dql = "
            SELECT r FROM Claroline\CoreBundle\Entity\Role r
            JOIN r.workspaceRights wsr
            JOIN wsr.workspace ws
            JOIN r.users u
            WHERE u.id = {$user->getId()}
            AND ws.id = {$workspace->getId()}
            ";

        $query = $this->_em->createQuery($dql);
        $result = $query->getOneOrNullResult();

        return ($result === null) ? true: false;
    }
}