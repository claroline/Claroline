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

use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Class UserRepository.
 */
class UserRepository extends EntityRepository implements UserProviderInterface
{
    /**
     * @var PlatformConfigurationHandler
     */
    private $platformConfigHandler;

    /**
     * @param PlatformConfigurationHandler $platformConfigHandler
     *
     * @DI\InjectParams({
     *      "platformConfigHandler" = @DI\Inject("claroline.config.platform_config_handler")
     * })
     */
    public function setPlatformConfigurationHandler(PlatformConfigurationHandler $platformConfigHandler)
    {
        $this->platformConfigHandler = $platformConfigHandler;
    }

    public function findOneBy(array $criteria = null, array $orderBy = null)
    {
        $trueFilter = [];

        foreach ($criteria as $prop => $value) {
            if ('email' === $prop) {
                $trueFilter['email'] = $value;
            } else {
                $trueFilter[$prop] = $value;
            }
        }

        return parent::findOneBy($trueFilter, $orderBy);
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        $isUserAdminCodeUnique = $this->platformConfigHandler->getParameter('is_user_admin_code_unique');

        $dql = '
            SELECT u FROM Claroline\CoreBundle\Entity\User u
            WHERE u.username LIKE :username
            OR u.email LIKE :username
        ';

        if ($isUserAdminCodeUnique) {
            $dql .= '
                OR u.administrativeCode LIKE :username';
        }
        $dql .= '
            AND u.isEnabled = true';
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
     * {@inheritdoc}
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
            WHERE u.id = :id
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('id', $user->getId());
        $user = $query->getSingleResult();

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return $this->getEntityName() === $class || is_subclass_of($class, $this->getEntityName());
    }

    /**
     * Returns all the users.
     *
     * @param bool   $executeQuery
     * @param string $orderedBy
     * @param null   $order
     *
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
                WHERE u.isRemoved = false
                ORDER BY u.{$orderedBy} {$order}
            ";

            return $this->_em->createQuery($dql);
        }

        return parent::findAll();
    }

    /**
     * Search users whose first name, last name or username match a given search string.
     *
     * @param string $search
     * @param bool   $executeQuery
     * @param string $orderedBy
     * @param null   $order
     *
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
            WHERE (
            UPPER(u.lastName) LIKE :search
            OR UPPER(u.firstName) LIKE :search
            OR UPPER(u.username) LIKE :search
            OR UPPER(u.administrativeCode) LIKE :search
            OR UPPER(u.email) LIKE :search
            OR CONCAT(UPPER(u.firstName), CONCAT(' ', UPPER(u.lastName))) LIKE :search
            OR CONCAT(UPPER(u.lastName), CONCAT(' ', UPPER(u.firstName))) LIKE :search
            )
            AND u.isRemoved = false
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
     * @param Group  $group
     * @param bool   $executeQuery
     * @param string $orderedBy
     *
     * @return User[]|Query
     */
    public function findByGroup(
        Group $group,
        $executeQuery = true,
        $orderedBy = 'id',
        $order = 'ASC'
    ) {
        $dql = "
            SELECT DISTINCT u FROM Claroline\CoreBundle\Entity\User u
            JOIN u.groups g
            WHERE g.id = :groupId
            AND u.isRemoved = false
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
     * @param string $search
     * @param Group  $group
     * @param bool   $executeQuery
     * @param string $orderedBy
     *
     * @return User[]|Query
     */
    public function findByNameAndGroup(
        $search,
        Group $group,
        $executeQuery = true,
        $orderedBy = 'id',
        $order = 'ASC'
    ) {
        $dql = "
            SELECT DISTINCT u FROM Claroline\CoreBundle\Entity\User u
            JOIN u.groups g
            WHERE g.id = :groupId
            AND (UPPER(u.username) LIKE :search
            OR UPPER(u.lastName) LIKE :search
            OR UPPER(u.firstName) LIKE :search)
            AND u.isRemoved = false
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
     * @param bool           $executeQuery
     *
     * @return User[]|\Doctrine\ORM\QueryBuilder
     */
    public function findUsersByWorkspace($workspace, $executeQuery = true)
    {
        $userQueryBuilder = $this->createQueryBuilder('u')
            ->select('u')
            ->join('u.roles', 'r')
            ->andWhere('u.isRemoved = false')
            ->orderBy('u.id');

        if (null === $workspace) {
            $userQueryBuilder->andWhere('r.workspace IS NULL');
        } else {
            $userQueryBuilder
                ->leftJoin('r.workspace', 'w')
                ->andWhere('r.workspace = :workspace')
                ->setParameter('workspace', $workspace);
        }

        return $executeQuery ? $userQueryBuilder->getQuery()->getResult() : $userQueryBuilder->getQuery();
    }

    /**
     * Returns the users who are members of one of the given workspaces. Users's groups are not
     * taken into account.
     *
     * @param array $workspaces
     * @param bool  $executeQuery
     *
     * @return User[]|Query
     */
    public function findUsersByWorkspaces(array $workspaces, $executeQuery = true)
    {
        // First find user ids, then retrieve users it's much faster this way, with UNION select in SQL
        $sql = 'SELECT DISTINCT u.id AS id FROM (
                  SELECT u1.id AS id FROM claro_user u1
                  INNER JOIN claro_user_role ur1 ON u1.id = ur1.user_id
                  INNER JOIN claro_role r1 ON r1.id = ur1.role_id
                  WHERE r1.workspace_id IN (:workspaces) AND u1.is_removed = :removed
                  UNION
                  SELECT u2.id AS id FROM claro_user u2
                  INNER JOIN claro_user_group ug2 ON u2.id = ug2.user_id
                  INNER JOIN claro_group g2 ON g2.id = ug2.group_id
                  INNER JOIN claro_group_role gr2 ON g2.id = gr2.group_id
                  INNER JOIN claro_role r2 ON r2.id = gr2.role_id
                  WHERE r2.workspace_id IN (:workspaces) AND u2.is_removed = :removed
                  ) u
                ';
        $rsm = new Query\ResultSetMapping();
        $rsm->addScalarResult('id', 'id', 'integer');
        $userIds = array_column($this->_em->createNativeQuery($sql, $rsm)
            ->setParameter('workspaces', $workspaces)
            ->setParameter('removed', false)
            ->getScalarResult(), 'id');

        $dql = '
            SELECT DISTINCT u FROM Claroline\CoreBundle\Entity\User u
            WHERE u.id IN (:ids)
            ORDER BY u.id
        ';

        $query = $this->_em
            ->createQuery($dql)
            ->setParameter('ids', $userIds)
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);

        return $executeQuery ? $query->getResult() : $query;
    }

    /**
     * Returns the users who are members of one of the given workspaces.
     * User list is filtered by a search on first name, last name and username.
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
            LEFT JOIN u.groups g
            LEFT JOIN g.roles gr
            LEFT JOIN gr.workspace gw
            LEFT JOIN wr.workspace w
            WHERE (
              w IN (:workspaces) OR
              gw IN (:workspaces)
            )
            AND (
                UPPER(u.firstName) LIKE :search
                OR UPPER(u.lastName) LIKE :search
                OR UPPER(u.username) LIKE :search
            )
            AND u.isRemoved = false
            ORDER BY u.id
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspaces', $workspaces);
        $query->setParameter('search', "%{$upperSearch}%");

        return $query->getResult();
    }

    /**
     * Returns the users of a workspace whose first name, last name or username
     * match a given search string. Including users in groups.
     *
     * @param Workspace $workspace
     * @param string    $search
     * @param bool      $executeQuery
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
                SELECT pr1 from Claroline\CoreBundle\Entity\Role pr1 WHERE pr1.type = :type
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
            AND u1.isRemoved = false
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
            AND u2.isRemoved = false)
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspaceId', $workspace->getId())
            ->setParameter('search', "%{$upperSearch}%");
        $query->setParameter('type', Role::WS_ROLE);

        return $executeQuery ? $query->getResult() : $query;
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
                WHERE u.isRemoved = false
                AND u.username IN (:usernames)
            ';

            $query = $this->_em->createQuery($dql);
            $query->setParameter('usernames', $usernames);
            $result = $query->getResult();
        } else {
            $result = [];
        }

        return $result;
    }

    /**
     * Returns enabled users by their usernames.
     *
     * @param array $usernames
     *
     * @return User[]
     */
    public function findEnabledUsersByUsernames(array $usernames)
    {
        if (count($usernames) > 0) {
            $dql = '
                SELECT u FROM Claroline\CoreBundle\Entity\User u
                WHERE u.isRemoved = false
                AND u.isEnabled = true
                AND u.username IN (:usernames)
            ';

            $query = $this->_em->createQuery($dql);
            $query->setParameter('usernames', $usernames);
            $result = $query->getResult();
        } else {
            $result = [];
        }

        return $result;
    }

    /**
     * Counts the users subscribed in a platform role.
     *
     * @param $role
     * @param $restrictionRoleNames
     *
     * @return int
     */
    public function countUsersByRole(Role $role, $restrictionRoleNames = null)
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

    public function countUsers()
    {
        $qb = $this->createQueryBuilder('user')
            ->select('COUNT(user.id)')
            ->where('user.isRemoved = false');

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Returns user Ids that are subscribed to one of the roles given.
     *
     * @param array $roleNames
     *
     * @return array
     */
    public function findUserIdsInRoles($roleNames)
    {
        $qb = $this->createQueryBuilder('user')
            ->select('user.id')
            ->leftJoin('user.roles', 'roles')
            ->andWhere('roles.name IN (:roleNames)')
            ->andWhere('user.isRemoved = false')
            ->setParameter('roleNames', $roleNames);
        $query = $qb->getQuery();

        return $query->getArrayResult();
    }

    /**
     * Returns the first name, last name, username and number of workspaces of
     * each user enrolled in at least one workspace.
     *
     * @param int $max
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
            AND u.isRemoved = false
            GROUP BY u.id
            ORDER BY total DESC, name ASC
        ";

        $query = $this->_em->createQuery($dql);

        if ($max > 1) {
            $query->setMaxResults($max);
        }

        return $query->getResult();
    }

    /**
     * @param Role[] $roles
     * @param bool   $getQuery
     *
     * @return Query|User[]
     */
    public function findByRoles(array $roles, $getQuery = false)
    {
        $dql = "
            SELECT u FROM Claroline\CoreBundle\Entity\User u
            JOIN u.roles r WHERE r IN (:roles) AND u.isRemoved = false
            ORDER BY u.lastName
            ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('roles', $roles);

        return ($getQuery) ? $query : $query->getResult();
    }

    /**
     * @param Role[] $roles
     * @param bool   $getQuery
     * @param string $orderedBy
     * @param $order
     *
     * @return Query|User[]
     */
    public function findByRolesIncludingGroups(array $roles, $getQuery = false, $orderedBy = 'id', $order = '')
    {
        // First find user ids, then retrieve users it's much faster this way, with UNION select in SQL
        $sql = 'SELECT DISTINCT u.id AS id FROM (
                  SELECT u1.id AS id FROM claro_user u1
                  INNER JOIN claro_user_role ur1 ON u1.id = ur1.user_id
                  WHERE ur1.role_id IN (:roles) AND u1.is_removed = :removed
                  UNION
                  SELECT u2.id AS id FROM claro_user u2
                  INNER JOIN claro_user_group ug2 ON u2.id = ug2.user_id
                  INNER JOIN claro_group g2 ON g2.id = ug2.group_id
                  INNER JOIN claro_group_role gr2 ON g2.id = gr2.group_id
                  WHERE gr2.role_id IN (:roles) AND u2.is_removed = :removed
                  ) u
                ';
        $rsm = new Query\ResultSetMapping();
        $rsm->addScalarResult('id', 'id', 'integer');
        $userIds = array_column($this->_em->createNativeQuery($sql, $rsm)
            ->setParameter('roles', $roles)
            ->setParameter('removed', false)
            ->getScalarResult(), 'id');

        $dql = "
            SELECT u, g, r1, r2 From Claroline\CoreBundle\Entity\User u
            JOIN u.roles r1
            LEFT JOIN u.groups g
            LEFT JOIN g.roles r2
            WHERE u.id in (:ids)
            ORDER BY u.{$orderedBy} ".
            $order;
        $query = $this->_em->createQuery($dql);
        $query
            ->setParameter('ids', $userIds)
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);

        return ($getQuery) ? $query : $query->getResult();
    }

    /**
     * @param Role[] $roles
     * @param bool   $getQuery
     * @param string $orderedBy
     *
     * @return Query|User[]
     */
    public function findUsersByRolesIncludingGroups(
        array $roles
    ) {
        //very slow otherwise. If we want to do it properly, the OR clause won't do it.
        //we must use UNION wich is not supported by Doctrine
        $dql = '
            SELECT u, r1, ws
            From Claroline\\CoreBundle\\Entity\\User u
            LEFT JOIN u.roles r1
            LEFT JOIN u.personalWorkspace ws
            WHERE r1 in (:roles)
            AND u.isRemoved = false
            ORDER BY u.lastName, u.firstName ASC
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('roles', $roles);

        $resA = $query->getResult();
        $resA = $resA ? $resA : [];

        $dql = '
            SELECT u, g, r2, ws
            From Claroline\\CoreBundle\\Entity\\User u
            LEFT JOIN u.personalWorkspace ws
            LEFT JOIN u.groups g
            LEFT JOIN g.roles r2
            WHERE r2 in (:roles)
            AND u.isRemoved = false
            ORDER BY u.lastName, u.firstName ASC
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('roles', $roles);

        $resB = $query->getResult();
        $resB = $resB ? $resB : [];

        return array_merge($resA, $resB);
    }

    /**
     * @param Role[] $roles
     * @param string $name
     * @param bool   $getQuery
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
            AND u.isRemoved = false
            ORDER BY u.lastName
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('roles', $roles);
        $query->setParameter('search', "%{$search}%");

        return ($getQuery) ? $query : $query->getResult();
    }

    /**
     * @param Role[] $roles
     * @param string $name
     * @param bool   $getQuery
     * @param string $orderedBy
     * @param null   $order
     *
     * @return Query|User[]
     */
    public function findByRolesAndNameIncludingGroups(array $roles, $name, $getQuery = false, $orderedBy = 'id', $order = null)
    {
        $search = strtoupper($name);

        // First find user ids, then retrieve users it's much faster this way, with UNION select in SQL
        $sql = 'SELECT DISTINCT u.id AS id FROM (
                  SELECT u1.id AS id FROM claro_user u1
                  INNER JOIN claro_user_role ur1 ON u1.id = ur1.user_id
                  WHERE ur1.role_id IN (:roles)
                  AND u1.is_removed = :removed
                  AND (
                    UPPER(u1.last_name) LIKE :search
                    OR UPPER(u1.first_name) LIKE :search
                    OR UPPER(u1.username) LIKE :search
                    OR UPPER (u1.email) LIKE :search
                  )
                  UNION
                  SELECT u2.id AS id FROM claro_user u2
                  INNER JOIN claro_user_group ug2 ON u2.id = ug2.user_id
                  INNER JOIN claro_group g2 ON g2.id = ug2.group_id
                  INNER JOIN claro_group_role gr2 ON g2.id = gr2.group_id
                  WHERE gr2.role_id IN (:roles)
                  AND u2.is_removed = :removed
                  AND (
                    UPPER(u2.last_name) LIKE :search
                    OR UPPER(u2.first_name) LIKE :search
                    OR UPPER(u2.username) LIKE :search
                    OR UPPER (u2.email) LIKE :search
                  )
                  ) u
                ';
        $rsm = new Query\ResultSetMapping();
        $rsm->addScalarResult('id', 'id', 'integer');
        $userIds = array_column($this->_em->createNativeQuery($sql, $rsm)
            ->setParameter('roles', $roles)
            ->setParameter('removed', false)
            ->setParameter('search', "%{$search}%")
            ->getScalarResult(), 'id');

        $dql = "
            SELECT u, ur, g, gr FROM Claroline\CoreBundle\Entity\User u
            JOIN u.roles ur
            LEFT JOIN u.groups g
            LEFT JOIN g.roles gr
            WHERE u.id IN (:ids)
            ORDER BY u.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query
            ->setParameter('ids', $userIds)
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);

        return ($getQuery) ? $query : $query->getResult();
    }

    /**
     * Returns the first name, last name, username and number of created workspaces
     * of each user who has created at least one workspace.
     *
     * @param int $max
     *
     * @return array
     */
    public function findUsersOwnersOfMostWorkspaces($max)
    {
        $dql = "
            SELECT CONCAT(CONCAT(u.firstName,' '), u.lastName) AS name, u.username, COUNT(DISTINCT ws.id) AS total
            FROM Claroline\CoreBundle\Entity\Workspace\Workspace ws
            JOIN ws.creator u
            WHERE u.isRemoved = false
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
            OR u.email = :email
            AND u.isRemoved = false
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('username', $username);
        $query->setParameter('email', $email);

        return $query->getResult();
    }

    /**
     * @param string $search
     *
     * @return array
     */
    public function findByNameForAjax($search)
    {
        $resultArray = [];

        $users = $this->findByName($search);

        foreach ($users as $user) {
            $resultArray[] = [
                'id' => $user->getId(),
                'text' => $user->getFirstName().' '.$user->getLastName(),
            ];
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
        if (null !== $search) {
            $query = $this->findByName($search, false);

            return $query
                ->setFirstResult(0)
                ->setMaxResults(10)
                ->getResult();
        }

        return [];
    }

    public function findUsernames()
    {
        $dql = "SELECT u.username as username FROM Claroline\CoreBundle\Entity\User u";
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function findEmails()
    {
        $dql = "SELECT u.email as email FROM Claroline\CoreBundle\Entity\User u";
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
            AND u.isRemoved = false
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('id', $data);
        $query->setParameter('publicUrl', $data);

        return $query->getSingleResult();
    }

    public function countUsersByRoleIncludingGroup(Role $role)
    {
        $sql = '
            SELECT count(distinct usr.id) AS total
            FROM (
              SELECT u1.id AS id
              FROM claro_user u1
              INNER JOIN claro_user_role ur1 ON u1.id = ur1.user_id
              WHERE ur1.role_id = :roleId
              UNION
              SELECT u2.id AS id
              FROM claro_user u2
              INNER JOIN claro_user_group ug2 ON u2.id = ug2.user_id
              INNER JOIN claro_group g2 ON g2.id = ug2.group_id
              INNER JOIN claro_group_role gr2 ON g2.id = gr2.group_id
              WHERE gr2.role_id = :roleId
            ) AS usr
        ';

        $rsm = new Query\ResultSetMapping();
        $rsm->addScalarResult('total', 'total', 'integer');

        $query = $this->_em->createNativeQuery($sql, $rsm);
        $query->setParameter('roleId', $role->getId());

        return (int) $query->getSingleScalarResult();
    }

    public function countUsersOfGroup(Group $group)
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
            WHERE u.isRemoved = false
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

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findUsersWithRights(
        ResourceNode $node,
        $orderedBy = 'firstName',
        $order = 'ASC',
        $executeQuery = true
    ) {
        $dql = "
            SELECT u
            FROM Claroline\CoreBundle\Entity\User u
            WHERE u.isRemoved = false
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

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findUsersWithoutRights(
        ResourceNode $node,
        $orderedBy = 'firstName',
        $order = 'ASC',
        $executeQuery = true
    ) {
        $dql = "
            SELECT u
            FROM Claroline\CoreBundle\Entity\User u
            WHERE u.isRemoved = false
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

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findSearchedUsersWithRights(
        ResourceNode $node,
        $search = '',
        $orderedBy = 'firstName',
        $order = 'ASC',
        $executeQuery = true
    ) {
        $dql = "
            SELECT u
            FROM Claroline\CoreBundle\Entity\User u
            WHERE u.isRemoved = false
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

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findSearchedUsersWithoutRights(
        ResourceNode $node,
        $search = '',
        $orderedBy = 'firstName',
        $order = 'ASC',
        $executeQuery = true
    ) {
        $dql = "
            SELECT u
            FROM Claroline\CoreBundle\Entity\User u
            WHERE u.isRemoved = false
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

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findAllWithFacets()
    {
        $dql = "
            SELECT u, ff
            FROM Claroline\CoreBundle\Entity\User u
            LEFT JOIN u.fieldsFacetValue ff
            WHERE u.isRemoved = false"
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
            AND u.isRemoved = false
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

    public function findUserByUsernameOrMail($username, $email, $executeQuery = true)
    {
        $dql = '
            SELECT u
            FROM Claroline\CoreBundle\Entity\User u
            WHERE u.username = :username
            OR u.email = :email
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('username', $username);
        $query->setParameter('email', $email);

        return $executeQuery ? $query->getOneOrNullResult() : $query;
    }

    public function findUsersByUsernamesOrMails($usernames, $mails, $executeQuery = true)
    {
        $dql = '
            SELECT u
            FROM Claroline\CoreBundle\Entity\User u
            WHERE u.username IN (:usernames)
            OR u.email IN (:mails)
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('usernames', $usernames);
        $query->setParameter('mails', $mails);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findUserByUsernameOrMailOrCode($username, $email, $code)
    {
        $dql = '
            SELECT u
            FROM Claroline\CoreBundle\Entity\User u
            WHERE u.username = :username
            OR u.email = :email
            OR u.administrativeCode = :code
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('username', $username);
        $query->setParameter('email', $email);
        $query->setParameter('code', $code);

        return $query->getOneOrNullResult();
    }

    public function findUserByUsernameAndMail($username, $email, $executeQuery = true)
    {
        $dql = '
            SELECT u
            FROM Claroline\CoreBundle\Entity\User u
            WHERE u.username = :username
            AND u.email = :email
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('username', $username);
        $query->setParameter('email', $email);

        return $executeQuery ? $query->getOneOrNullResult() : $query;
    }

    public function countAllEnabledUsers($executeQuery = true)
    {
        $dql = '
            SELECT COUNT(DISTINCT u)
            FROM Claroline\CoreBundle\Entity\User u
            WHERE u.isRemoved = false
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
                AND u.isRemoved = false
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
        array $roleRestrictions = [],
        array $groupRestrictions = [],
        array $workspaceRestrictions = [],
        array $excludedUsers = [],
        array $forcedUsers = [],
        array $forcedGroups = [],
        array $forcedRoles = [],
        array $forcedWorkspaces = [],
        $withOrganizations = false,
        array $forcedOrganizations = [],
        $executeQuery = true
    ) {
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
        ';

        if ($withOrganizations) {
            $dql .= '
                JOIN u.organizations o
            ';
        }
        $dql .= '
            WHERE u.isRemoved = false
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
                    OR UPPER(u.email) LIKE :search
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
        if ($withOrganizations) {
            $dql .= '
                AND o IN (:forcedOrganizations)
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
        if ($withOrganizations) {
            $query->setParameter('forcedOrganizations', $forcedOrganizations);
        }

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findForApi($data)
    {
        $dql = 'SELECT u FROM Claroline\CoreBundle\Entity\User u
            WHERE u.id = :data
            OR u.username LIKE :data
            OR u.email LIKE :data';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('data', $data);

        return $query->getOneOrNullResult();
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
        if ('' !== $search) {
            $dql = '
                SELECT u
                FROM Claroline\CoreBundle\Entity\User u
                WHERE UPPER(u.firstName) LIKE :search
                OR UPPER(u.lastName) LIKE :search
                OR UPPER(u.username) LIKE :search
                AND u.isRemoved = false
            ';
            $query = $this->_em->createQuery($dql);
            $query->setParameter('search', "%{$upperSearch}%");

            return $query->getResult();
        }

        return parent::findAll();
    }

    /**
     * Returns the users who are not members of a group.
     *
     * @param Group  $group
     * @param bool   $executeQuery
     * @param string $orderedBy
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
            AND u.isRemoved = false
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
     * @param string                             $search
     * @param bool                               $executeQuery
     * @param string                             $orderedBy
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
            AND u.isRemoved = false
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
            AND u.isRemoved = false
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('userIds', $excludedUser);

        return $query->getResult();
    }

    /**
     * Returns the users who are members of one of the given workspaces. Users's groups ARE
     * taken into account.
     *
     * @param Workspace $workspace
     * @param bool      $executeQuery
     *
     * @return array
     */
    public function findByWorkspaceWithUsersFromGroup(Workspace $workspace, $executeQuery = true)
    {
        $dql = '
            SELECT u
            FROM Claroline\CoreBundle\Entity\User u
            JOIN u.roles ur
            LEFT JOIN u.groups g
            LEFT JOIN g.roles gr
            LEFT JOIN gr.workspace grws
            LEFT JOIN ur.workspace uws
            WHERE (uws.id = :wsId
            OR grws.id = :wsId)
            AND u.isRemoved = false
         ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('wsId', $workspace->getId());

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findUsersExcludingRoles(array $roles, $offset, $limit)
    {
        $dql = '
            SELECT u FROM Claroline\CoreBundle\Entity\User u
            WHERE u.isRemoved = false AND u NOT IN (
                SELECT u2 FROM Claroline\CoreBundle\Entity\User u2
                LEFT JOIN u2.roles ur
                LEFT JOIN u2.groups g
                LEFT JOIN g.roles gr
                WHERE (gr IN (:roles) OR ur IN (:roles))

            )
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('roles', $roles);
        $query->setFirstResult($offset);
        $query->setMaxResults($limit);

        return $query->getResult();
    }

    /**
     * Finds users with a list of IDs.
     *
     * @param array $ids
     *
     * @return User[]
     */
    public function findByIds(array $ids)
    {
        return $this->getEntityManager()
            ->createQuery('
                SELECT u FROM Claroline\CoreBundle\Entity\User u
                WHERE u IN (:ids)
                  AND u.isRemoved = false
                  AND u.isEnabled = true
            ')
            ->setParameter('ids', $ids)
            ->getResult();
    }

    public function countUsersNotManagersOfPersonalWorkspace()
    {
        $query = $this->getEntityManager()
            ->createQuery('
                SELECT COUNT(u.id) AS cnt FROM Claroline\CoreBundle\Entity\User u
                INNER JOIN u.personalWorkspace ws
                WHERE u.isRemoved = :notRemoved
                AND ws.personal = :personal
                AND u.id NOT IN ('.$this->findUsersManagersOfPersonalWorkspace(false)->getDQL().')
            ')
            ->setParameter('notRemoved', false)
            ->setParameter('personal', true);

        return intval($query->getResult()[0]['cnt']);
    }

    public function findUsersNotManagersOfPersonalWorkspace($offset = null, $limit = null)
    {
        $query = $this->getEntityManager()
            ->createQuery('
                SELECT u, ws FROM Claroline\CoreBundle\Entity\User u
                INNER JOIN u.personalWorkspace ws
                WHERE u.isRemoved = :notRemoved
                AND ws.personal = :personal
                AND u.id NOT IN ('.$this->findUsersManagersOfPersonalWorkspace(false)->getDQL().')
            ')
            ->setParameter('notRemoved', false)
            ->setParameter('personal', true)
            ->setMaxResults($limit);

        if ($offset) {
            $query->setFirstResult($offset);
        }

        return $query->getResult();
    }

    public function findUsersManagersOfPersonalWorkspace($execute = true)
    {
        $query = $this->getEntityManager()
            ->createQuery('
                SELECT u1.id FROM Claroline\CoreBundle\Entity\User u1
                INNER JOIN u1.personalWorkspace ws1
                INNER JOIN ws1.roles r1
                INNER JOIN r1.users us1
                WHERE us1.id = u1.id
                AND u1.isRemoved = :notRemoved
                AND ws1.personal = :personal
                AND r1.name LIKE \'%ROLE_WS_MANAGER_%\'
            ')
            ->setParameter('notRemoved', false)
            ->setParameter('personal', true);

        return $execute ? $query->getResult() : $query;
    }
}
