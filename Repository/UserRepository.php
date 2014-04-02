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

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Group;
use Doctrine\ORM\Query;
use Claroline\CoreBundle\Persistence\MissingObjectException;

class UserRepository extends EntityRepository implements UserProviderInterface
{
    const PLATEFORM_ROLE = 1;
    const WORKSPACE_ROLE = 2;
    const ALL_ROLES = 3;

    /**
     * @{inheritDoc}
     */
    public function loadUserByUsername($username)
    {
        $dql = '
            SELECT u FROM Claroline\CoreBundle\Entity\User u
            WHERE u.username LIKE :username
            OR u.mail LIKE :username
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('username', $username);

        try {
            $user = $query->getSingleResult();
        } catch (NoResultException $e) {
            throw new UsernameNotFoundException(
                sprintf('Unable to find an active user identified by "%s".', $username)
            );
        }

        return $user;
    }

    /**
     * @{inheritDoc}
     */
    public function refreshUser(UserInterface $user)
    {

        $class = get_class($user);

        if (!$this->supportsClass($class)) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $class));
        }

        $dql = '
            SELECT u, groups, group_roles, roles, ws, gws FROM Claroline\CoreBundle\Entity\User u
            LEFT JOIN u.groups groups
            LEFT JOIN groups.roles group_roles
            LEFT JOIN u.roles roles
            LEFT JOIN roles.workspace ws
            LEFT JOIN group_roles.workspace gws
            WHERE u.id = :userId
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('userId', $user->getId());
        $user = $query->getSingleResult();

        return $user;
    }

    /**
     * @{inheritDoc}
     */
    public function supportsClass($class)
    {
        return $this->getEntityName() === $class || is_subclass_of($class, $this->getEntityName());
    }

    /**
     * Returns the users who have a given workspace role. The members of a group
     * which has that role are also returned.
     *
     * @param AbstractWorkspace $workspace
     * @param Role              $role
     *
     * @return User[]
     */
    public function findByWorkspaceAndRole(AbstractWorkspace $workspace, Role $role)
    {
        $dql = '
            SELECT DISTINCT u FROM Claroline\CoreBundle\Entity\User u
            LEFT JOIN u.roles wr WITH wr IN (
                SELECT pr from Claroline\CoreBundle\Entity\Role pr WHERE pr.type = ' . Role::WS_ROLE . "
            )
            LEFT JOIN wr.workspace w
            WHERE w.id = {$workspace->getId()}
            AND u.isEnabled = true
            AND wr.id = {$role->getId()}
        ";
        $query = $this->_em->createQuery($dql);
        $userResults = $query->getResult();

        $dql = '
            SELECT DISTINCT u FROM Claroline\CoreBundle\Entity\User u
            JOIN u.groups g
            JOIN g.roles wr WITH wr IN (
                SELECT pr from Claroline\CoreBundle\Entity\Role pr WHERE pr.type = '. Role::WS_ROLE . "
            )
            LEFT JOIN wr.workspace w
            WHERE w.id = {$workspace->getId()}
            AND wr.id = {$role->getId()}
        ";
        $query = $this->_em->createQuery($dql);
        $groupResults = $query->getResult();

        return array_merge($userResults, $groupResults);
    }

    /**
     * Returns the users who are not members of a workspace. Users's groups are not
     * taken into account.
     *
     * @param AbstractWorkspace $workspace
     * @param boolean           $executeQuery
     *
     * @return User[]|Query
     */
    public function findWorkspaceOutsiders(AbstractWorkspace $workspace, $executeQuery = true)
    {
        $dql = '
            SELECT u, ws, r FROM Claroline\CoreBundle\Entity\User u
            LEFT JOIN u.personalWorkspace ws
            LEFT JOIN u.roles r
            WITH r IN (SELECT pr from Claroline\CoreBundle\Entity\Role pr WHERE pr.type = ' . Role::WS_ROLE . ')
            WHERE u NOT IN
            (
                SELECT us FROM Claroline\CoreBundle\Entity\User us
                LEFT JOIN us.roles wr WITH wr IN (
                    SELECT pr2 from Claroline\CoreBundle\Entity\Role pr2 WHERE pr2.type = ' . Role::WS_ROLE . '
                )
                LEFT JOIN wr.workspace w
                WHERE w.id = :id
            )
            AND u.isEnabled = true
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('id', $workspace->getId());

        return $executeQuery ? $query->getResult() : $query;
    }

    /**
     * Returns the users who are not members of a workspace, filtered by a search on
     * their name. Users's groups are not taken into account.
     *
     * @param AbstractWorkspace $workspace
     * @param string            $search
     * @param boolean           $executeQuery
     *
     * @return User[]|Query
     */
    public function findWorkspaceOutsidersByName(AbstractWorkspace $workspace, $search, $executeQuery = true)
    {
        $dql = '
            SELECT u, ws, r FROM Claroline\CoreBundle\Entity\User u
            LEFT JOIN u.personalWorkspace ws
            LEFT JOIN u.roles r
            WITH r IN (SELECT pr from Claroline\CoreBundle\Entity\Role pr WHERE pr.type = ' . Role::WS_ROLE . ')
            WHERE u NOT IN
            (
                SELECT us FROM Claroline\CoreBundle\Entity\User us
                LEFT JOIN us.roles wr WITH wr IN (
                    SELECT pr2 from Claroline\CoreBundle\Entity\Role pr2 WHERE pr2.type = ' . Role::WS_ROLE . '
                )
                LEFT JOIN wr.workspace w
                WHERE w.id = :id
            )
            AND ( UPPER(u.firstName) LIKE :search
                OR UPPER(u.lastName) LIKE :search
                OR UPPER(u.username) LIKE :search
            )
            AND u.isEnabled = true
        ';
        $upperSearch = strtoupper($search);
        $query = $this->_em->createQuery($dql);
        $query->setParameter('id', $workspace->getId());
        $query->setParameter('search', "%{$upperSearch}%");

        return $executeQuery ? $query->getResult() : $query;
    }

    /**
     * Returns all the users.
     *
     * @param boolean $executeQuery
     * @param string  $orderedBy
     *
     * @return User[]|Query
     */
    public function findAll($executeQuery = true, $orderedBy = 'id', $order = null)
    {
        if (!$executeQuery) {
            $order = $order === 'DESC' ? 'DESC' : 'ASC';
            $dql = "
                SELECT u, pws, g, r , rws from Claroline\CoreBundle\Entity\User u
                LEFT JOIN u.personalWorkspace pws
                LEFT JOIN u.groups g
                LEFT JOIN u.roles r
                LEFT JOIN r.workspace rws
                WHERE u.isEnabled = true
                ORDER BY u.{$orderedBy}
                ".$order

            ;
            // the join on role is required because this method is only called in the administration
            // and we only want the platform roles of a user.
            return $this->_em->createQuery($dql);
        }

        return parent::findAll();
    }

    /**
     * Returns all the users by search.
     *
     * @param string $search
     *
     * @return User[]
     */
    public function findAllUserBySearch($search)
    {
        $upperSearch = strtoupper(trim($search));

        if ($search !== '') {
            $dql = '
                SELECT u
                FROM Claroline\CoreBundle\Entity\User u
                WHERE UPPER(u.firstName) LIKE :search
                OR UPPER(u.lastName) LIKE :search
                OR UPPER(u.username) LIKE :search
                AND u.isEnabled = true
            ';

            $query = $this->_em->createQuery($dql);
            $query->setParameter('search', "%{$upperSearch}%");

            return $query->getResult();
        }

        return parent::findAll();
    }

    /**
     * Search users whose first name, last name or username match a given search string.
     *
     * @param string  $search
     * @param boolean $executeQuery
     * @param string  $orderedBy
     *
     * @return User[]|Query
     */
    public function findByName($search, $executeQuery = true, $orderedBy = 'id', $order = null)
    {
        $order = $order === 'DESC' ? 'DESC' : 'ASC';
        $upperSearch = strtoupper($search);
        $upperSearch = trim($upperSearch);
        $upperSearch = preg_replace('/\s+/', ' ', $upperSearch);
        $dql = "
            SELECT u, r, pws, g, rws FROM Claroline\CoreBundle\Entity\User u
            LEFT JOIN u.personalWorkspace pws
            LEFT JOIN u.groups g
            LEFT JOIN u.roles r
            LEFT JOIN r.workspace rws
            WHERE UPPER(u.lastName) LIKE :search
            OR UPPER(u.firstName) LIKE :search
            OR UPPER(u.username) LIKE :search
            OR UPPER(u.administrativeCode) LIKE :search
            OR UPPER(u.mail) LIKE :search
            OR CONCAT(UPPER(u.firstName), CONCAT(' ', UPPER(u.lastName))) LIKE :search
            OR CONCAT(UPPER(u.lastName), CONCAT(' ', UPPER(u.firstName))) LIKE :search
            AND u.isEnabled = true
            ORDER BY u.{$orderedBy}
            ".$order
        ;
        $query = $this->_em->createQuery($dql);
        $query->setParameter('search', "%{$upperSearch}%");

        return $executeQuery ? $query->getResult() : $query;
    }

    /**
     * Returns the users of a group.
     *
     * @param Group   $group
     * @param boolean $executeQuery
     * @param string  $orderedBy
     *
     * @return User[]|Query
     */
    public function findByGroup(Group $group, $executeQuery = true, $orderedBy = 'id')
    {
        $dql = '
            SELECT DISTINCT u, g, pw, r from Claroline\CoreBundle\Entity\User u
            JOIN u.groups g
            LEFT JOIN u.personalWorkspace pw
            LEFT JOIN u.roles r WITH r IN (
                SELECT pr from Claroline\CoreBundle\Entity\Role pr WHERE pr.type = ' . Role::PLATFORM_ROLE . "
            )
            WHERE g.id = :groupId AND u.isEnabled = true ORDER BY u.{$orderedBy}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('groupId', $group->getId());

        return $executeQuery ? $query->getResult() : $query;
    }

    /**
     * Returns the users of a group whose first name, last name or username match
     * a given search string.
     *
     * @param string  $search
     * @param Group   $group
     * @param boolean $executeQuery
     * @param string  $orderedBy
     *
     * @return User[]|Query
     */
    public function findByNameAndGroup($search, Group $group, $executeQuery = true, $orderedBy = 'id')
    {
        $dql = '
            SELECT DISTINCT u, g, pw, r from Claroline\CoreBundle\Entity\User u
            JOIN u.groups g
            LEFT JOIN u.personalWorkspace pw
            LEFT JOIN u.roles r WITH r IN (
                SELECT pr from Claroline\CoreBundle\Entity\Role pr WHERE pr.type = ' . Role::PLATFORM_ROLE . "
            )
            WHERE g.id = :groupId
            AND (UPPER(u.username) LIKE :search
            OR UPPER(u.lastName) LIKE :search
            OR UPPER(u.firstName) LIKE :search)
            AND u.isEnabled = true
            ORDER BY u.{$orderedBy}
        ";
        $upperSearch = strtoupper($search);
        $query = $this->_em->createQuery($dql);
        $query->setParameter('search', "%{$upperSearch}%");
        $query->setParameter('groupId', $group->getId());

        return $executeQuery ? $query->getResult() : $query;
    }

    /**
     * Returns the users who are members of a workspace. Users's groups are not
     * taken into account.
     *
     * @param AbstractWorkspace $workspace
     * @param boolean           $executeQuery
     *
     * @return User[]|Query
     */
    public function findByWorkspace(AbstractWorkspace $workspace, $executeQuery = true)
    {
        $dql = '
            SELECT wr, u, ws from Claroline\CoreBundle\Entity\User u
            JOIN u.roles wr WITH wr IN (
                SELECT pr from Claroline\CoreBundle\Entity\Role pr WHERE pr.type = ' . Role::WS_ROLE . '
            )
            LEFT JOIN wr.workspace w
            LEFT JOIN u.personalWorkspace ws
            WHERE w.id = :workspaceId
            AND u.isEnabled = true
            ORDER BY u.id
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspaceId', $workspace->getId());

        return $executeQuery ? $query->getResult() : $query;
    }

    /**
     * Returns the users who are members of one of the given workspaces. Users's groups are not
     * taken into account.
     *
     * @param array   $workspaces
     * @param boolean $executeQuery
     *
     * @return User[]|Query
     */
    public function findUsersByWorkspaces(array $workspaces,$executeQuery = true)
    {
        $dql = '
            SELECT DISTINCT u from Claroline\CoreBundle\Entity\User u
            JOIN u.roles wr WITH wr IN (
                SELECT pr from Claroline\CoreBundle\Entity\Role pr WHERE pr.type = ' . Role::WS_ROLE . '
            )
            LEFT JOIN wr.workspace w
            LEFT JOIN u.personalWorkspace ws
            WHERE w IN (:workspaces)
            AND u.isEnabled = true
            ORDER BY u.id
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspaces', $workspaces);

        return $executeQuery ? $query->getResult() : $query;
    }

    /**
     * Returns the users who are members of one of the given workspaces.
     * User list is filtered by a search on first name, last name and username
     *
     * @param array  $workspaces
     * @param string $search
     *
     * @return User[]
     */
    public function findUsersByWorkspacesAndSearch(array $workspaces, $search)
    {
        $upperSearch = strtoupper(trim($search));

        $dql = '
            SELECT DISTINCT u from Claroline\CoreBundle\Entity\User u
            JOIN u.roles wr WITH wr IN (
                SELECT pr
                FROM Claroline\CoreBundle\Entity\Role pr
                WHERE pr.type = ' . Role::WS_ROLE . '
            )
            LEFT JOIN wr.workspace w
            LEFT JOIN u.personalWorkspace ws
            WHERE w IN (:workspaces)
            AND  (
                UPPER(u.firstName) LIKE :search
                OR UPPER(u.lastName) LIKE :search
                OR UPPER(u.username) LIKE :search
            )
            AND u.isEnabled = true
            ORDER BY u.id
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspaces', $workspaces);
        $query->setParameter('search', "%{$upperSearch}%");

        return $query->getResult();
    }

    /**
     * Returns the users of a workspace whose first name, last name or username
     * match a given search string.
     *
     * @param AbstractWorkspace $workspace
     * @param string            $search
     * @param boolean           $executeQuery
     *
     * @return User[]|Query
     */
    public function findByWorkspaceAndName(AbstractWorkspace $workspace, $search, $executeQuery = true)
    {
        $upperSearch = strtoupper($search);
        $dql = '
            SELECT u, r, ws FROM Claroline\CoreBundle\Entity\User u
            JOIN u.roles r WITH r IN (
                SELECT pr from Claroline\CoreBundle\Entity\Role pr WHERE pr.type = ' . Role::WS_ROLE . '
            )
            LEFT JOIN r.workspace wol
            LEFT JOIN u.personalWorkspace ws
            WHERE wol.id = :workspaceId AND u IN (
                SELECT us FROM Claroline\CoreBundle\Entity\User us
                WHERE UPPER(us.lastName) LIKE :search
                OR UPPER(us.firstName) LIKE :search
                OR UPPER(us.username) LIKE :search
            )
            AND u.isEnabled = true
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspaceId', $workspace->getId())
              ->setParameter('search', "%{$upperSearch}%");

        return $executeQuery ? $query->getResult() : $query;
    }

    /**
     * Returns the users of a workspace whose first name, last name or username
     * match a given search string. Including users in groups
     *
     * @param AbstractWorkspace $workspace
     * @param string            $search
     * @param boolean           $executeQuery
     *
     * @return User[]|Query
     */
    public function findAllByWorkspaceAndName(AbstractWorkspace $workspace, $search, $executeQuery = true)
    {
        $upperSearch = strtoupper($search);
        $dql = '
            SELECT DISTINCT u FROM Claroline\CoreBundle\Entity\User u
            WHERE u IN (
            SELECT u1 FROM Claroline\CoreBundle\Entity\User u1
            JOIN u1.roles r1 WITH r1 IN (
                SELECT pr1 from Claroline\CoreBundle\Entity\Role pr1 WHERE pr1.type = ' . Role::WS_ROLE . '
            )
            LEFT JOIN r1.workspace wol1
            WHERE wol1.id = :workspaceId AND u1 IN (
                SELECT us1 FROM Claroline\CoreBundle\Entity\User us1
                WHERE UPPER(us1.lastName) LIKE :search
                OR UPPER(us1.firstName) LIKE :search
                OR UPPER(us1.username) LIKE :search
                OR CONCAT(UPPER(us1.firstName), CONCAT(\' \', UPPER(us1.lastName))) LIKE :search
                OR CONCAT(UPPER(us1.lastName), CONCAT(\' \', UPPER(us1.firstName))) LIKE :search
            )
            AND u1.isEnabled = true
            )
            OR u IN (
            SELECT u2 FROM Claroline\CoreBundle\Entity\User u2
            JOIN u2.groups g2
            JOIN g2.roles r2 WITH r2 IN (
                SELECT pr2 from Claroline\CoreBundle\Entity\Role pr2 WHERE pr2.type = ' . Role::WS_ROLE . '
            )
            LEFT JOIN r2.workspace wol2
            WHERE wol2.id = :workspaceId AND u IN (
                SELECT us2 FROM Claroline\CoreBundle\Entity\User us2
                WHERE UPPER(us2.lastName) LIKE :search
                OR UPPER(us2.firstName) LIKE :search
                OR UPPER(us2.username) LIKE :search
                OR CONCAT(UPPER(us2.firstName), CONCAT(\' \', UPPER(us2.lastName))) LIKE :search
                OR CONCAT(UPPER(us2.lastName), CONCAT(\' \', UPPER(us2.firstName))) LIKE :search
            )
            AND u2.isEnabled = true)
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspaceId', $workspace->getId())
            ->setParameter('search', "%{$upperSearch}%");

        return $executeQuery ? $query->getResult() : $query;
    }

    /**
     * Returns the users who are not members of a group.
     *
     * @param Group   $group
     * @param boolean $executeQuery
     * @param string  $orderedBy
     *
     * @return User[]|Query
     */
    public function findGroupOutsiders(Group $group, $executeQuery = true, $orderedBy = 'id')
    {
        $dql = '
            SELECT DISTINCT u, ws, r FROM Claroline\CoreBundle\Entity\User u
            LEFT JOIN u.personalWorkspace ws
            LEFT JOIN u.roles r WITH r IN (
                SELECT pr from Claroline\CoreBundle\Entity\Role pr WHERE pr.type = ' . Role::PLATFORM_ROLE . "
            )
            WHERE u NOT IN (
                SELECT us FROM Claroline\CoreBundle\Entity\User us
                JOIN us.groups gs
                WHERE gs.id = :groupId
            )
            AND u.isEnabled = true
            ORDER BY u.{$orderedBy}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('groupId', $group->getId());

        return $executeQuery ? $query->getResult() : $query;
    }

    /**
     * Returns the users who are not members of a group and whose first name, last
     * name or username match a given search string.
     *
     * @param AbstractWorkspace $workspace
     * @param string            $search
     * @param boolean           $executeQuery
     * @param string            $orderedBy
     *
     * @return User[]|Query
     */
    public function findGroupOutsidersByName(Group $group, $search, $executeQuery = true, $orderedBy = 'id')
    {
        $dql = '
            SELECT DISTINCT u, ws, r FROM Claroline\CoreBundle\Entity\User u
            LEFT JOIN u.personalWorkspace ws
            LEFT JOIN u.roles r WITH r IN (
                SELECT pr from Claroline\CoreBundle\Entity\Role pr WHERE pr.type = ' . Role::PLATFORM_ROLE . "
            )
            WHERE (
                UPPER(u.lastName) LIKE :search
                OR UPPER(u.firstName) LIKE :search
                OR UPPER(u.lastName) LIKE :search
            )
            AND u NOT IN (
                SELECT us FROM Claroline\CoreBundle\Entity\User us
                JOIN us.groups gr
                WHERE gr.id = :groupId
            )
            AND u.isEnabled = true
            ORDER BY u.{$orderedBy}
        ";
        $search = strtoupper($search);
        $query = $this->_em->createQuery($dql);
        $query->setParameter('groupId', $group->getId());
        $query->setParameter('search', "%{$search}%");

        return $executeQuery ? $query->getResult() : $query;
    }

    /**
     * Returns all the users except a given one.
     *
     * @param User $excludedUser
     *
     * @return User[]
     */
    public function findAllExcept(User $excludedUser)
    {
        $dql = '
            SELECT u FROM Claroline\CoreBundle\Entity\User u
            WHERE u.id <> :userId
            AND u.isEnabled = true
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('userId', $excludedUser->getId());

        return $query->getResult();
    }

    /**
     * Returns users by their usernames.
     *
     * @param array $usernames
     *
     * @return User[]
     *
     * @throws MissingObjectException if one or more users cannot be found
     */
    public function findByUsernames(array $usernames)
    {
        $usernameCount = count($usernames);
        $firstUsername = array_pop($usernames);
        $dql = '
            SELECT u FROM Claroline\CoreBundle\Entity\User u
            WHERE u.username = :user_first
            AND u.isEnabled = true
        ';

        foreach ($usernames as $key => $username) {
            $dql .= " OR u.username = :user_{$key}" . PHP_EOL;
        }

        $query = $this->_em->createQuery($dql);
        $query->setParameter('user_first', $firstUsername);

        foreach ($usernames as $key => $username) {
            $query->setParameter('user_' . $key, $username);
        }

        $result = $query->getResult();

        if (($userCount = count($result)) !== $usernameCount) {
            throw new MissingObjectException("{$userCount} out of {$usernameCount} users were found");
        }

        return $result;
    }

    /**
     * Counts the users.
     *
     * @return integer
     */
    public function count()
    {
        $dql = "SELECT COUNT(u) FROM Claroline\CoreBundle\Entity\User u";
        $query = $this->_em->createQuery($dql);

        return $query->getSingleScalarResult();
    }

    /**
     * Counts the users subscribed in a platform role
     *
     * @return integer
     */
    public function countUsersByRole($role, $restrictionRoleNames)
    {
        $qb = $this->createQueryBuilder('user')
            ->select('COUNT(DISTINCT user.id)')
            ->leftJoin('user.roles', 'roles')
            ->andWhere('roles.id = :roleId')
            ->setParameter('roleId', $role->getId());
        if (!empty($restrictionRoleNames)) {
            $qb->andWhere('user.id NOT IN (:userIds)')
                ->setParameter('userIds', $this->findUserIdsInRoles($restrictionRoleNames));
        }
        $query = $qb->getQuery();

        return $query->getSingleScalarResult();
    }

    /**
     * Returns user Ids that are subscribed to one of the roles given
     * @param  array $roleNames
     * @return array
     */
    public function findUserIdsInRoles($roleNames)
    {
        $qb = $this->createQueryBuilder('user')
            ->select('user.id')
            ->leftJoin('user.roles', 'roles')
            ->andWhere('roles.name IN (:roleNames)')
            ->andWhere('user.isEnabled = true')
            ->setParameter('roleNames', $roleNames);
        $query = $qb->getQuery();

        return $query->getArrayResult();
    }

    /**
     * Returns the first name, last name, username and number of workspaces of
     * each user enrolled in at least one workspace.
     *
     * @param integer $max
     *
     * @return User[]
     */
    public function findUsersEnrolledInMostWorkspaces($max)
    {
        $dql = "
            SELECT CONCAT(CONCAT(u.firstName, ' '), u.lastName) AS name, u.username, COUNT(DISTINCT ws.id) AS total
            FROM Claroline\CoreBundle\Entity\User u, Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace ws
            WHERE CONCAT(CONCAT(u.id,':'), ws.id) IN
            (
                SELECT CONCAT(CONCAT(u1.id, ':'), ws1.id)
                FROM Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace ws1
                JOIN ws1.roles r1
                JOIN r1.users u1
            ) OR CONCAT(CONCAT(u.id, ':'), ws.id) IN
            (
                SELECT CONCAT(CONCAT(u2.id, ':'), ws2.id)
                FROM Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace ws2
                JOIN ws2.roles r2
                JOIN r2.groups g2
                JOIN g2.users u2
            )
            AND u.isEnabled = true
            GROUP BY u.id
            ORDER BY total DESC
        ";

        $query = $this->_em->createQuery($dql);

        if ($max > 1) {
            $query->setMaxResults($max);
        }

        return $query->getResult();
    }

    /**
     * @param Role[]  $roles
     * @param boolean $getQuery
     *
     * @return Query|User[]
     */
    public function findByRoles(array $roles, $getQuery = false)
    {
        $dql = "
            SELECT u FROM Claroline\CoreBundle\Entity\User u
            JOIN u.roles r WHERE r IN (:roles) AND u.isEnabled = true
            ORDER BY u.lastName
            ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('roles', $roles);

        return ($getQuery) ? $query: $query->getResult();
    }

    /**
     * @param Role[]  $roles
     * @param boolean $getQuery
     * @param string  $orderedBy
     *
     * @return Query|User[]
     */
    public function findByRolesIncludingGroups(array $roles, $getQuery = false, $orderedBy = 'id', $order)
    {
        $order = $order === 'DESC' ? 'DESC' : 'ASC';
        $dql = "
            SELECT u, r1, g, r2, ws From Claroline\CoreBundle\Entity\User u
            LEFT JOIN u.roles r1
            LEFT JOIN u.personalWorkspace ws
            LEFT JOIN u.groups g
            LEFT JOIN g.roles r2
            WHERE r1 in (:roles)
            AND u.isEnabled = true
            OR r2 in (:roles)
            ORDER BY u.{$orderedBy} ".
            $order;

        $query = $this->_em->createQuery($dql);
        $query->setParameter('roles', $roles);

        return ($getQuery) ? $query: $query->getResult();
    }

    /**
     * @param Role[]  $roles
     * @param string  $name
     * @param boolean $getQuery
     *
     * @return Query|User[]
     */
    public function findByRolesAndName(array $roles, $name, $getQuery = false)
    {
        $search = strtoupper($name);
        $dql = "
            SELECT u FROM Claroline\CoreBundle\Entity\User u
            JOIN u.roles r WHERE r IN (:roles)
            AND (UPPER(u.username) LIKE :search
            OR UPPER(u.lastName) LIKE :search
            OR UPPER(u.firstName) LIKE :search)
            AND u.isEnabled = true
            ORDER BY u.lastName
            ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('roles', $roles);
        $query->setParameter('search', "%{$search}%");

        return ($getQuery) ? $query: $query->getResult();
    }

    /**
     * @param Role[]  $roles
     * @param string  $name
     * @param boolean $getQuery
     * @param strinf  $orderedBy
     *
     * @return Query|User[]
     */
    public function findByRolesAndNameIncludingGroups(array $roles, $name, $getQuery = false, $orderedBy = 'id', $order = null)
    {
        $order = $order === 'DESC' ? 'DESC' : 'ASC';
        $search = strtoupper($name);
        $dql = "
            SELECT u, r1, g, r2, pws FROM Claroline\CoreBundle\Entity\User u
            LEFT JOIN u.roles r1
            JOIN u.personalWorkspace pws
            LEFT JOIN u.groups g
            LEFT JOIN g.roles r2
            WHERE (r1 IN (:roles)
            OR r2 IN (:roles))
            AND (
            UPPER(u.lastName) LIKE :search
            OR UPPER(u.firstName) LIKE :search)
            AND u.isEnabled = true
            ORDER BY u.{$orderedBy}
            ".$order;

        $query = $this->_em->createQuery($dql);
        $query->setParameter('roles', $roles);
        $query->setParameter('search', "%{$search}%");

        return ($getQuery) ? $query: $query->getResult();
    }

    /**
     * This method should be renamed.
     * Find users who are outside the workspace and users whose role are in $roles.
     *
     * @param Role[]                                                   $roles
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param boolean                                                  $getQuery
     *
     * @return Query|User[]
     */
    public function findOutsidersByWorkspaceRoles(array $roles, AbstractWorkspace $workspace, $getQuery = false)
    {
        //feel free to make this request easier if you can

        $dql = "
            SELECT u FROM Claroline\CoreBundle\Entity\User u
            WHERE u NOT IN (
                SELECT u2 FROM Claroline\CoreBundle\Entity\User u2
                JOIN u2.roles r WHERE r IN (:roles) AND
                u2 NOT IN (
                    SELECT u3 FROM Claroline\CoreBundle\Entity\User u3
                    JOIN u3.roles r2
                    JOIN r2.workspace ws
                    WHERE r2 NOT IN (:roles)
                    AND ws = :wsId
                )
            )
            AND u.isEnabled = true
            ORDER BY u.lastName
            ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('roles', $roles);
        $query->setParameter('wsId', $workspace);

        return $getQuery ? $query : $query->getResult();
    }

    /**
     * This method should be renamed.
     * Find users who are outside the workspace and users whose role are in $roles.
     *
     * @param Role[]                                                   $roles
     * @param string                                                   $name
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param boolean                                                  $getQuery
     *
     * @return Query|User[]
     */
    public function findOutsidersByWorkspaceRolesAndName(
        array $roles,
        $name,
        AbstractWorkspace $workspace,
        $getQuery = false
    )
    {
        //feel free to make this request easier if you can
        $search = strtoupper($name);

        $dql = "
            SELECT u FROM Claroline\CoreBundle\Entity\User u
            WHERE u NOT IN (
                SELECT u2 FROM Claroline\CoreBundle\Entity\User u2
                JOIN u2.roles r WHERE r IN (:roles) AND
                u2 NOT IN (
                    SELECT u3 FROM Claroline\CoreBundle\Entity\User u3
                    JOIN u3.roles r2
                    JOIN r2.workspace ws
                    WHERE r2 NOT IN (:roles)
                    AND ws = :wsId
                )
            )
            AND UPPER(u.username) LIKE :search
            OR UPPER(u.lastName) LIKE :search
            OR UPPER(u.firstName) LIKE :search
            AND u.isEnabled = true
            ORDER BY u.lastName
            ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('roles', $roles);
        $query->setParameter('wsId', $workspace);
        $query->setParameter('search', "%{$search}%");

        return $getQuery ? $query : $query->getResult();
    }

    /**
     * Returns the first name, last name, username and number of created workspaces
     * of each user who has created at least one workspace.
     *
     * @param integer $max
     *
     * @return array
     */
    public function findUsersOwnersOfMostWorkspaces($max)
    {
        $dql = "
            SELECT CONCAT(CONCAT(u.firstName,' '), u.lastName) AS name, u.username, COUNT(DISTINCT ws.id) AS total
            FROM Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace ws
            JOIN ws.creator u
            WHERE u.isEnabled = true
            GROUP BY u.id
            ORDER BY total DESC
        ";
        $query = $this->_em->createQuery($dql);

        if ($max > 1) {
            $query->setMaxResults($max);
        }

        return $query->getResult();
    }

    /**
     * @param string $username
     * @param string $email
     *
     * @return User
     */
    public function findUserByUsernameOrEmail($username, $email)
    {
        $dql = '
            SELECT u
            FROM Claroline\CoreBundle\Entity\User u
            WHERE u.username = :username
            OR u.mail = :email
            AND u.isEnabled = true
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('username', $username);
        $query->setParameter('email', $email);

        return $query->getResult();
    }

    /**
     * @param AbstractWorkspace $workspace
     *
     * @return array
     */
    public function findByWorkspaceWithUsersFromGroup(AbstractWorkspace $workspace)
    {
        $dql = '
            SELECT u
            FROM Claroline\CoreBundle\Entity\User u
            LEFT JOIN u.groups g
            LEFT JOIN g.roles gr
            LEFT JOIN gr.workspace grws
            LEFT JOIN u.roles ur
            LEFT JOIN ur.workspace uws
            WHERE uws.id = :wsId
            OR grws.id = :wsId
         ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('wsId', $workspace->getId());
        $res = $query->getResult();

        return $res;
    }

    /**
     * @param string $search
     *
     * @return array
     */
    public function findByNameForAjax($search)
    {
        $resultArray = array();

        $users = $this->findByName($search);

        foreach ($users as $user) {
            $resultArray[] = array(
                'id'   => $user->getId(),
                'text' => $user->getFirstName() . ' ' . $user->getLastName()
            );
        }

        return $resultArray;
    }

    /**
     * @param array $params
     *
     * @return User[]
     */
    public function extract($params)
    {
        $search = $params['search'];
        if ($search !== null) {

            $query = $this->findByName($search, false);

            return $query
                ->setFirstResult(0)
                ->setMaxResults(10)
                ->getResult();
        }

        return array();
    }

    public function findUsernames()
    {
        $dql = "SELECT u.username as username FROM Claroline\CoreBundle\Entity\User u";
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function findEmails()
    {
        $dql = "SELECT u.mail as mail FROM Claroline\CoreBundle\Entity\User u";
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function findCodes()
    {
        $dql = "SELECT u.administrativeCode as code FROM Claroline\CoreBundle\Entity\User u";
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    /**
     * @param bool $executeQuery
     *
     * @return array|Query
     */
    public function findWithPublicProfilePreferences($executeQuery = true)
    {
        $queryBuilder = $this->createQueryBuilder('user')
            ->leftJoin('user.publicProfilePreferences', 'uppf');

        return $executeQuery ? $queryBuilder->getQuery()->getResult(): $queryBuilder->getQuery();
    }
}
