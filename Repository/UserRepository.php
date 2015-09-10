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
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Model\WorkspaceModel;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Doctrine\ORM\Query;

class UserRepository extends EntityRepository implements UserProviderInterface
{
    /**
     * @{inheritDoc}
     */
    public function loadUserByUsername($username)
    {
        $dql = '
            SELECT u FROM Claroline\CoreBundle\Entity\User u
            WHERE u.username LIKE :username
            OR u.mail LIKE :username
            OR u.administrativeCode LIKE :username
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
            SELECT u, ur, g, gr FROM Claroline\CoreBundle\Entity\User u
            JOIN u.roles ur
            LEFT JOIN u.groups g
            LEFT JOIN g.roles gr
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
     * Returns all the users.
     *
     * @param boolean $executeQuery
     * @param string $orderedBy
     * @param null $order
     * @return User[]|Query
     */
    public function findAll($executeQuery = true, $orderedBy = 'id', $order = null)
    {
        if (!$executeQuery) {
            $dql = "
                SELECT u, pws, g, r, rws
                FROM Claroline\CoreBundle\Entity\User u
                LEFT JOIN u.personalWorkspace pws
                LEFT JOIN u.groups g
                LEFT JOIN u.roles r
                LEFT JOIN r.workspace rws
                WHERE u.isEnabled = true
                ORDER BY u.{$orderedBy} {$order}
            ";

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
     * @param string $search
     * @param boolean $executeQuery
     * @param string $orderedBy
     * @param null $order
     * @return User[]|Query
     */
    public function findByName($search, $executeQuery = true, $orderedBy = 'id', $order = null)
    {
        $upperSearch = strtoupper($search);
        $upperSearch = trim($upperSearch);
        $upperSearch = preg_replace('/\s+/', ' ', $upperSearch);
        $dql = "
            SELECT u, r, g FROM Claroline\CoreBundle\Entity\User u
            JOIN u.roles r
            LEFT JOIN u.groups g
            WHERE UPPER(u.lastName) LIKE :search
            OR UPPER(u.firstName) LIKE :search
            OR UPPER(u.username) LIKE :search
            OR UPPER(u.administrativeCode) LIKE :search
            OR UPPER(u.mail) LIKE :search
            OR CONCAT(UPPER(u.firstName), CONCAT(' ', UPPER(u.lastName))) LIKE :search
            OR CONCAT(UPPER(u.lastName), CONCAT(' ', UPPER(u.firstName))) LIKE :search
            AND u.isEnabled = true
            AND r.type = 1
            ORDER BY u.{$orderedBy} {$order}
        ";
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
    public function findByGroup(
        Group $group,
        $executeQuery = true,
        $orderedBy = 'id',
        $order = 'ASC'
    )
    {
        $dql = "
            SELECT DISTINCT u FROM Claroline\CoreBundle\Entity\User u
            JOIN u.groups g
            WHERE g.id = :groupId
            AND u.isEnabled = true
            ORDER BY u.{$orderedBy} {$order}
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
    public function findByNameAndGroup(
        $search,
        Group $group,
        $executeQuery = true,
        $orderedBy = 'id',
        $order = 'ASC'
    )
    {
        $dql = "
            SELECT DISTINCT u FROM Claroline\CoreBundle\Entity\User u
            JOIN u.groups g
            WHERE g.id = :groupId
            AND (UPPER(u.username) LIKE :search
            OR UPPER(u.lastName) LIKE :search
            OR UPPER(u.firstName) LIKE :search)
            AND u.isEnabled = true
            ORDER BY u.{$orderedBy} {$order}
        ";
        $upperSearch = strtoupper($search);
        $query = $this->_em->createQuery($dql);
        $query->setParameter('search', "%{$upperSearch}%");
        $query->setParameter('groupId', $group->getId());

        return $executeQuery ? $query->getResult() : $query;
    }

    /**
     * Returns the users who are members of one of the given workspaces. Users's groups are not
     * taken into account.
     *
     * @param Workspace|null $workspace
     * @param boolean        $executeQuery
     *
     * @return User[]|\Doctrine\ORM\QueryBuilder
     */
    public function findUsersByWorkspace($workspace, $executeQuery = true)
    {
        $userQueryBuilder = $this->createQueryBuilder('u')
            ->select('u')
            ->join('u.roles', 'r')
            ->andWhere('u.isEnabled = true')
            ->orderBy('u.id');

        if (null === $workspace) {
            $userQueryBuilder->andWhere('r.workspace IS NULL');
        }
        else {
            $userQueryBuilder
                ->leftJoin('r.workspace', 'w')
                ->andWhere('r.workspace = :workspace')
                ->setParameter('workspace', $workspace);
        };

        return $executeQuery ? $userQueryBuilder->getQuery()->getResult() : $userQueryBuilder->getQuery();
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
    public function findUsersByWorkspaces(array $workspaces, $executeQuery = true)
    {
        $dql = '
            SELECT DISTINCT u FROM Claroline\CoreBundle\Entity\User u
            JOIN u.roles wr
            LEFT JOIN wr.workspace w
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
            SELECT DISTINCT u FROM Claroline\CoreBundle\Entity\User u
            JOIN u.roles wr
            LEFT JOIN wr.workspace w
            WHERE w IN (:workspaces)
            AND (
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
     * match a given search string. Including users in groups
     *
     * @param Workspace $workspace
     * @param string            $search
     * @param boolean           $executeQuery
     *
     * @return User[]|Query
     */
    public function findAllByWorkspaceAndName(Workspace $workspace, $search, $executeQuery = true)
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
                SELECT pr2 from Claroline\CoreBundle\Entity\Role pr2 WHERE pr2.type = :type
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
        $query->setParameter('type', Role::WS_ROLE);

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
     *
     * @todo Find out why the join on profile preferences is necessary
     */
    public function findGroupOutsiders(Group $group, $executeQuery = true, $orderedBy = 'id')
    {
        $dql = "
            SELECT DISTINCT u FROM Claroline\CoreBundle\Entity\User u
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
     * @param \Claroline\CoreBundle\Entity\Group $group
     * @param string $search
     * @param boolean $executeQuery
     * @param string $orderedBy
     *
     * @return User[]|Query
     *
     * @todo Find out why the join on profile preferences is necessary
     */
    public function findGroupOutsidersByName(Group $group, $search, $executeQuery = true, $orderedBy = 'id')
    {
        $dql = "
            SELECT DISTINCT u FROM Claroline\CoreBundle\Entity\User u
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
     * @param array $excludedUser
     *
     * @return User[]
     */
    public function findAllExcept(array $excludedUser)
    {
        $dql = '
            SELECT u FROM Claroline\CoreBundle\Entity\User u
            WHERE u NOT IN (:userIds)
            AND u.isEnabled = true
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('userIds', $excludedUser);

        return $query->getResult();
    }

    /**
     * Returns users by their usernames.
     *
     * @param array $usernames
     *
     * @return User[]
     */
    public function findByUsernames(array $usernames)
    {
        if (count($usernames) > 0) {
            $dql = '
                SELECT u FROM Claroline\CoreBundle\Entity\User u
                WHERE u.isEnabled = true
                AND u.username IN (:usernames)
            ';

            $query = $this->_em->createQuery($dql);
            $query->setParameter('usernames', $usernames);
            $result = $query->getResult();
        } else {
            $result = array();
        }

        return $result;
    }

    /**
     * Counts the users subscribed in a platform role
     *
     * @param $role
     * @param $restrictionRoleNames
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
            FROM Claroline\CoreBundle\Entity\User u, Claroline\CoreBundle\Entity\Workspace\Workspace ws
            WHERE CONCAT(CONCAT(u.id,':'), ws.id) IN
            (
                SELECT CONCAT(CONCAT(u1.id, ':'), ws1.id)
                FROM Claroline\CoreBundle\Entity\Workspace\Workspace ws1
                JOIN ws1.roles r1
                JOIN r1.users u1
            ) OR CONCAT(CONCAT(u.id, ':'), ws.id) IN
            (
                SELECT CONCAT(CONCAT(u2.id, ':'), ws2.id)
                FROM Claroline\CoreBundle\Entity\Workspace\Workspace ws2
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
     * @param Role[] $roles
     * @param boolean $getQuery
     * @param string $orderedBy
     * @param $order
     *
     * @return Query|User[]
     */
    public function findByRolesIncludingGroups(array $roles, $getQuery = false, $orderedBy = 'id', $order = '')
    {
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
     * @param boolean $getQuery
     * @param string  $orderedBy
     *
     * @return Query|User[]
     */
    public function findUsersByRolesIncludingGroups(
        array $roles,
        $executeQuery = true
    )
    {
        $dql = "
            SELECT u, r1, g, r2, ws
            From Claroline\CoreBundle\Entity\User u
            LEFT JOIN u.roles r1
            LEFT JOIN u.personalWorkspace ws
            LEFT JOIN u.groups g
            LEFT JOIN g.roles r2
            WHERE r1 in (:roles)
            AND u.isEnabled = true
            OR r2 in (:roles)
            ORDER BY u.lastName, u.firstName ASC";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('roles', $roles);

        return ($executeQuery) ? $query->getResult() : $query;
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
        $dql = '
            SELECT u FROM Claroline\CoreBundle\Entity\User u
            JOIN u.roles r WHERE r IN (:roles)
            AND (UPPER(u.username) LIKE :search
            OR UPPER(u.lastName) LIKE :search
            OR UPPER(u.firstName) LIKE :search)
            AND u.isEnabled = true
            ORDER BY u.lastName
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('roles', $roles);

        return ($getQuery) ? $query: $query->getResult();
    }

    /**
     * @param Role[] $roles
     * @param string $name
     * @param boolean $getQuery
     * @param string $orderedBy
     * @param null $order
     *
     * @return Query|User[]
     */
    public function findByRolesAndNameIncludingGroups(array $roles, $name, $getQuery = false, $orderedBy = 'id', $order = null)
    {
        $search = strtoupper($name);
        $dql = "
            SELECT u, ur, g, gr FROM Claroline\CoreBundle\Entity\User u
            JOIN u.roles ur
            LEFT JOIN u.groups g
            LEFT JOIN g.roles gr
            WHERE u.isEnabled = true
            AND (
                ur IN (:roles) OR gr IN (:roles)
            )
            AND (
                UPPER(u.lastName) LIKE :search
                OR UPPER(u.firstName) LIKE :search
            )
            ORDER BY u.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('roles', $roles);
        $query->setParameter('search', "%{$search}%");

        return ($getQuery) ? $query: $query->getResult();
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
            FROM Claroline\CoreBundle\Entity\Workspace\Workspace ws
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
     * @param Workspace $workspace
     *
     * @return array
     */
    public function findByWorkspaceWithUsersFromGroup(Workspace $workspace)
    {
        $dql = '
            SELECT u
            FROM Claroline\CoreBundle\Entity\User u
            JOIN u.roles ur
            LEFT JOIN u.groups g
            LEFT JOIN g.roles gr
            LEFT JOIN gr.workspace grws
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
     * @param string $data
     *
     * @return User
     */
    public function findOneByIdOrPublicUrl($data)
    {
        $dql = '
            SELECT u
            FROM Claroline\CoreBundle\Entity\User u
            WHERE u.id = :id
            OR u.publicUrl = :publicUrl
            AND u.isEnabled = true
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('id', $data);
        $query->setParameter('publicUrl', $data);

        return $query->getSingleResult();
    }

    public function countUsersByRoleIncludingGroup(Role $role)
    {
        $dql = '
            SELECT count(distinct u)
            FROM Claroline\CoreBundle\Entity\User u
            JOIN u.roles r1
            LEFT JOIN  u.groups g
            LEFT JOIN g.roles r2
            WHERE r1.id = :roleId OR r2.id = :roleId
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('roleId', $role->getId());

        return $query->getSingleScalarResult();
    }

    public function countUsersOfGroup (Group $group)
    {
        $dql = '
            SELECT count(u) FROM Claroline\CoreBundle\Entity\User u
            JOIN u.groups g
            WHERE g.name = :name
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('name', $group->getName());

        return $query->getSingleScalarResult();
    }

    public function countUsersOfGroupByRole(Group $group, Role $role)
    {
        $dql = '
            SELECT count(u) FROM Claroline\CoreBundle\Entity\User u
            JOIN u.groups g
            WHERE g.name = :groupName
            AND u.id in
                (
                    SELECT u2.id FROM Claroline\CoreBundle\Entity\User u2
                    LEFT JOIN u2.roles r1
                    LEFT JOIN u2.groups g2
                    LEFT JOIN g2.roles r2
                    WHERE r1.name = :roleName
                    OR r2.name = :roleName
                )

        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('roleName', $role->getName());
        $query->setParameter('groupName', $group->getName());

        return $query->getSingleScalarResult();
    }

    /**
     * @todo Make the correct sql request
     * @param WorkspaceModel $model
     * @param bool $executeQuery
     * @return array|Query
     */
    public function findUsersNotSharingModel(WorkspaceModel $model, $executeQuery = true)
    {
        $dql = '
            SELECT u FROM Claroline\CoreBundle\Entity\User u
        ';

        $query = $this->_em->createQuery($dql);

        return $executeQuery ? $query->getResult(): $query;
    }

    /**
     * @todo Make the correct sql request
     * @param WorkspaceModel $model
     * @param $search
     * @param bool $executeQuery
     * @return array|Query
     */
    public function findUsersNotSharingModelBySearch(WorkspaceModel $model, $search, $executeQuery = true)
    {
        $search = strtoupper($search);

        $dql = '
            SELECT u FROM Claroline\CoreBundle\Entity\User u
            WHERE UPPER(u.lastName) LIKE :search
            OR UPPER(u.firstName) LIKE :search
            OR UPPER(u.username) LIKE :search
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('search', "%$search%");

        return $executeQuery ? $query->getResult(): $query;
    }

    public function findEnabledUserById($userId)
    {
        $dql = '
            SELECT u
            FROM Claroline\CoreBundle\Entity\User u
            WHERE u.id = :userId
            AND u.isEnabled = TRUE
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('userId', $userId);

        return $query->getOneOrNullResult();
    }

    public function findAllEnabledUsers($executeQuery = true)
    {
        $dql = '
            SELECT u
            FROM Claroline\CoreBundle\Entity\User u
            WHERE u.isEnabled = TRUE
        ';
        $query = $this->_em->createQuery($dql);
        return $executeQuery ? $query->getResult() : $query;
    }

    public function findUsersWithoutUserRole($executeQuery = true)
    {
        $dql = '
            SELECT u
            FROM Claroline\CoreBundle\Entity\User u
            WHERE u.isEnabled = true
            AND NOT EXISTS
            (
                SELECT r
                FROM Claroline\CoreBundle\Entity\Role r
                WHERE r.type = :type
                AND r.translationKey = u.username
            )
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('type', Role::USER_ROLE);

        return $executeQuery ? $query->getResult(): $query;
    }

    public function findUsersWithRights(
        ResourceNode $node,
        $orderedBy = 'firstName',
        $order = 'ASC',
        $executeQuery = true
    )
    {
        $dql = "
            SELECT u
            FROM Claroline\CoreBundle\Entity\User u
            WHERE u.isEnabled = true
            AND EXISTS
            (
                SELECT rr
                FROM Claroline\CoreBundle\Entity\Resource\ResourceRights rr
                JOIN rr.resourceNode rn
                JOIN rr.role r
                LEFT JOIN rr.resourceTypes rt
                WHERE rn = :resourceNode
                AND r.translationKey = u.username
                AND
                (
                    rr.mask > 0
                    OR EXISTS
                    (
                        SELECT rt2
                        FROM Claroline\CoreBundle\Entity\Resource\ResourceType rt2
                        WHERE rt2 = rt
                    )
                )
            )
            ORDER BY u.{$orderedBy} {$order}
        ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('resourceNode', $node);

        return $executeQuery ? $query->getResult(): $query;
    }

    public function findUsersWithoutRights(
        ResourceNode $node,
        $orderedBy = 'firstName',
        $order = 'ASC',
        $executeQuery = true
    )
    {
        $dql = "
            SELECT u
            FROM Claroline\CoreBundle\Entity\User u
            WHERE u.isEnabled = true
            AND NOT EXISTS
            (
                SELECT rr
                FROM Claroline\CoreBundle\Entity\Resource\ResourceRights rr
                JOIN rr.resourceNode rn
                JOIN rr.role r
                LEFT JOIN rr.resourceTypes rt
                WHERE rn = :resourceNode
                AND r.translationKey = u.username
                AND
                (
                    rr.mask > 0
                    OR EXISTS
                    (
                        SELECT rt2
                        FROM Claroline\CoreBundle\Entity\Resource\ResourceType rt2
                        WHERE rt2 = rt
                    )
                )
            )
            ORDER BY u.{$orderedBy} {$order}
        ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('resourceNode', $node);

        return $executeQuery ? $query->getResult(): $query;
    }

    public function findSearchedUsersWithRights(
        ResourceNode $node,
        $search = '',
        $orderedBy = 'firstName',
        $order = 'ASC',
        $executeQuery = true
    )
    {
        $dql = "
            SELECT u
            FROM Claroline\CoreBundle\Entity\User u
            WHERE u.isEnabled = true
            AND
            (
                UPPER(u.firstName) LIKE :search
                OR UPPER(u.lastName) LIKE :search
                OR UPPER(u.username) LIKE :search
            )
            AND EXISTS
            (
                SELECT rr
                FROM Claroline\CoreBundle\Entity\Resource\ResourceRights rr
                JOIN rr.resourceNode rn
                JOIN rr.role r
                LEFT JOIN rr.resourceTypes rt
                WHERE rn = :resourceNode
                AND r.translationKey = u.username
                AND
                (
                    rr.mask > 0
                    OR EXISTS
                    (
                        SELECT rt2
                        FROM Claroline\CoreBundle\Entity\Resource\ResourceType rt2
                        WHERE rt2 = rt
                    )
                )
            )
            ORDER BY u.{$orderedBy} {$order}
        ";

        $upperSearch = strtoupper($search);
        $query = $this->_em->createQuery($dql);
        $query->setParameter('resourceNode', $node);
        $query->setParameter('search', "%{$upperSearch}%");

        return $executeQuery ? $query->getResult(): $query;
    }

    public function findSearchedUsersWithoutRights(
        ResourceNode $node,
        $search = '',
        $orderedBy = 'firstName',
        $order = 'ASC',
        $executeQuery = true
    )
    {
        $dql = "
            SELECT u
            FROM Claroline\CoreBundle\Entity\User u
            WHERE u.isEnabled = true
            AND
            (
                UPPER(u.firstName) LIKE :search
                OR UPPER(u.lastName) LIKE :search
                OR UPPER(u.username) LIKE :search
            )
            AND NOT EXISTS
            (
                SELECT rr
                FROM Claroline\CoreBundle\Entity\Resource\ResourceRights rr
                JOIN rr.resourceNode rn
                JOIN rr.role r
                LEFT JOIN rr.resourceTypes rt
                WHERE rn = :resourceNode
                AND r.translationKey = u.username
                AND
                (
                    rr.mask > 0
                    OR EXISTS
                    (
                        SELECT rt2
                        FROM Claroline\CoreBundle\Entity\Resource\ResourceType rt2
                        WHERE rt2 = rt
                    )
                )
            )
            ORDER BY u.{$orderedBy} {$order}
        ";

        $upperSearch = strtoupper($search);
        $query = $this->_em->createQuery($dql);
        $query->setParameter('resourceNode', $node);
        $query->setParameter('search', "%{$upperSearch}%");

        return $executeQuery ? $query->getResult(): $query;
    }

    public function findAllWithFacets()
    {
        $dql = "
            SELECT u, ff
            FROM Claroline\CoreBundle\Entity\User u
            LEFT JOIN u.fieldsFacetValue ff
            WHERE u.isEnabled = true"
        ;

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function findAllWithFacetsByWorkspace(Workspace $workspace)
    {
        $dql = "
            SELECT u, ff
            FROM Claroline\CoreBundle\Entity\User u
            JOIN u.roles ur
            LEFT JOIN u.fieldsFacetValue ff
            LEFT JOIN u.groups g
            LEFT JOIN g.roles gr
            LEFT JOIN gr.workspace grws
            LEFT JOIN ur.workspace uws
            WHERE (uws.id = :wsId
            OR grws.id = :wsId)
            AND u.isEnabled = true
        ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('wsId', $workspace->getId());

        return $query->getResult();
    }

    public function findOneUserByUsername($username, $executeQuery = true)
    {
        $dql = '
            SELECT u
            FROM Claroline\CoreBundle\Entity\User u
            WHERE u.username = :username
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('username', $username);

        return $executeQuery ? $query->getOneOrNullResult() : $query;
    }

    public function findUserByUsernameOrMail($username, $mail, $executeQuery = true)
    {
        $dql = '
            SELECT u
            FROM Claroline\CoreBundle\Entity\User u
            WHERE u.username = :username
            OR u.mail = :mail
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('username', $username);
        $query->setParameter('mail', $mail);

        return $executeQuery ? $query->getOneOrNullResult() : $query;
    }

    public function findUserByUsernameAndMail($username, $mail, $executeQuery = true)
    {
        $dql = '
            SELECT u
            FROM Claroline\CoreBundle\Entity\User u
            WHERE u.username = :username
            AND u.mail = :mail
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('username', $username);
        $query->setParameter('mail', $mail);

        return $executeQuery ? $query->getOneOrNullResult() : $query;
    }

    public function countAllEnabledUsers($executeQuery = true)
    {
        $dql = '
            SELECT COUNT(DISTINCT u)
            FROM Claroline\CoreBundle\Entity\User u
            WHERE u.isEnabled = true
        ';

        $query = $this->_em->createQuery($dql);

        return $executeQuery ? $query->getSingleScalarResult() : $query;
    }

    public function countByRoles(array $roles, $includeGrps)
    {
        if ($includeGrps) {
            $dql = 'SELECT count (DISTINCT u)
                From Claroline\CoreBundle\Entity\User u
                LEFT JOIN u.roles r1
                LEFT JOIN u.personalWorkspace ws
                LEFT JOIN u.groups g
                LEFT JOIN g.roles r2
                WHERE r1 in (:roles)
                AND u.isEnabled = true
                OR r2 in (:roles)';

                $query = $this->_em->createQuery($dql);
                $query->setParameter('roles', $roles);

                return $query->getSingleScalarResult();
        }
    }

    public function findUsersForUserPicker(
        $search = '',
        $withUsername = true,
        $withMail = false,
        $withCode = false,
        $orderedBy = 'id',
        $order = 'ASC',
        array $roleRestrictions = array(),
        array $groupRestrictions = array(),
        array $workspaceRestrictions = array(),
        array $excludedUsers = array(),
        array $forcedUsers = array(),
        array $forcedGroups = array(),
        array $forcedRoles = array(),
        array $forcedWorkspaces = array(),
        $executeQuery = true
    )
    {
        $withSearch = !empty($search);
        $withGroups = count($groupRestrictions) > 0;
        $withRoles = count($roleRestrictions) > 0;
        $withWorkspaces = count($workspaceRestrictions) > 0;
        $withExcludedUsers = count($excludedUsers) > 0;
        $withForcedUsers = count($forcedUsers) > 0;
        $withForcedGroups = count($forcedGroups) > 0;
        $withForcedRoles = count($forcedRoles) > 0;
        $withForcedWorkspaces = count($forcedWorkspaces) > 0;

        $dql = '
            SELECT DISTINCT u
            FROM Claroline\CoreBundle\Entity\User u
            WHERE u.isEnabled = true
        ';

        if ($withGroups || $withRoles || $withWorkspaces) {
            $dql .= '
                AND (
            ';

            if ($withRoles) {
                $dql .= '
                    u IN (
                        SELECT ur
                        FROM Claroline\CoreBundle\Entity\User ur
                        JOIN ur.roles urr
                        WITH urr IN (:roleRestrictions)
                    )
                    OR u IN (
                        SELECT ur2
                        FROM Claroline\CoreBundle\Entity\User ur2
                        JOIN ur2.groups ur2g
                        JOIN ur2g.roles ur2gr
                        WITH ur2gr IN (:roleRestrictions)
                    )
                ';
            }

            if ($withGroups) {

                if ($withRoles) {
                    $dql .= 'OR';
                }

                $dql .= '
                    u IN (
                        SELECT ug
                        FROM Claroline\CoreBundle\Entity\User ug
                        JOIN ug.groups ugg
                        WITH ugg IN (:groupRestrictions)
                    )
                ';
            }

            if ($withWorkspaces) {

                if ($withRoles || $withGroups) {
                    $dql .= 'OR';
                }

                $dql .= '
                    u IN (
                        SELECT uw
                        FROM Claroline\CoreBundle\Entity\User uw
                        JOIN uw.roles uwr
                        WITH uwr.workspace IN (:workspaceRestrictions)
                    )
                    OR u IN (
                        SELECT uw2
                        FROM Claroline\CoreBundle\Entity\User uw2
                        JOIN uw2.groups uw2g
                        JOIN uw2g.roles uw2gr
                        WITH uw2gr.workspace IN (:workspaceRestrictions)
                    )
                ';
            }
            $dql .= '
                )
            ';
        }

        if ($withExcludedUsers) {
            $dql .= '
                AND u NOT IN (:excludedUsers)
            ';
        }

        if ($withForcedUsers) {
            $dql .= '
                AND u IN (:forcedUsers)
            ';
        }

        if ($withForcedGroups) {
            $dql .= '
                AND u IN (
                    SELECT ufg
                    FROM Claroline\CoreBundle\Entity\User ufg
                    JOIN ufg.groups ufgg
                    WITH ufgg IN (:forcedGroups)
                )
            ';
        }

        if ($withForcedRoles) {
            $dql .= '
                AND (
                    u IN (
                        SELECT ufr
                        FROM Claroline\CoreBundle\Entity\User ufr
                        JOIN ufr.roles ufrr
                        WITH ufrr IN (:forcedRoles)
                    )
                    OR u IN (
                        SELECT ufr2
                        FROM Claroline\CoreBundle\Entity\User ufr2
                        JOIN ufr2.groups ufr2g
                        JOIN ufr2g.roles ufr2gr
                        WITH ufr2gr IN (:forcedRoles)
                    )
                )
            ';
        }

        if ($withForcedWorkspaces) {
            $dql .= '
                AND (
                    u IN (
                        SELECT ufw
                        FROM Claroline\CoreBundle\Entity\User ufw
                        JOIN ufw.roles ufwr
                        WITH ufwr.workspace IN (:forcedWorkspaces)
                    )
                    OR u IN (
                        SELECT ufw2
                        FROM Claroline\CoreBundle\Entity\User ufw2
                        JOIN ufw2.groups ufw2g
                        JOIN ufw2g.roles ufw2gr
                        WITH ufw2gr.workspace IN (:forcedWorkspaces)
                    )
                )
            ';
        }

        if ($withSearch) {
            $dql .= '
                AND (
                    UPPER(u.firstName) LIKE :search
                    OR UPPER(u.lastName) LIKE :search
                    OR CONCAT(UPPER(u.firstName), CONCAT(\' \', UPPER(u.lastName))) LIKE :search
                    OR CONCAT(UPPER(u.lastName), CONCAT(\' \', UPPER(u.firstName))) LIKE :search
            ';

            if ($withUsername) {
                $dql .= '
                    OR UPPER(u.username) LIKE :search
                ';
            }

            if ($withMail) {
                $dql .= '
                    OR UPPER(u.mail) LIKE :search
                ';
            }

            if ($withCode) {
                $dql .= '
                    OR UPPER(u.administrativeCode) LIKE :search
                ';
            }
            $dql .= '
                )
            ';
        }
        $dql .= "
            ORDER BY u.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);

        if ($withGroups) {
            $query->setParameter('groupRestrictions', $groupRestrictions);
        }

        if ($withRoles) {
            $query->setParameter('roleRestrictions', $roleRestrictions);
        }

        if ($withWorkspaces) {
            $query->setParameter('workspaceRestrictions', $workspaceRestrictions);
        }

        if ($withForcedUsers) {
            $query->setParameter('forcedUsers', $forcedUsers);
        }

        if ($withForcedGroups) {
            $query->setParameter('forcedGroups', $forcedGroups);
        }

        if ($withForcedRoles) {
            $query->setParameter('forcedRoles', $forcedRoles);
        }

        if ($withForcedWorkspaces) {
            $query->setParameter('forcedWorkspaces', $forcedWorkspaces);
        }

        if ($withExcludedUsers) {
            $query->setParameter('excludedUsers', $excludedUsers);
        }

        if ($withSearch) {
            $upperSearch = strtoupper($search);
            $query->setParameter('search', "%{$upperSearch}%");
        }

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findForApi($data)
    {
        $dql = 'SELECT u FROM Claroline\CoreBundle\Entity\User u
            WHERE u.id = :data
            OR u.username LIKE :data
            OR u.mail LIKE :data';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('data', $data);

        return $query->getOneOrNullResult();
    }
}
