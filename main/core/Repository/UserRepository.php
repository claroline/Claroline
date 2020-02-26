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
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Class UserRepository.
 */
class UserRepository extends ServiceEntityRepository implements UserProviderInterface, UserLoaderInterface
{
    /** @var PlatformConfigurationHandler */
    private $platformConfigHandler;

    /**
     * UserRepository constructor.
     *
     * @param RegistryInterface            $registry
     * @param PlatformConfigurationHandler $platformConfigHandler
     */
    public function __construct(RegistryInterface $registry, PlatformConfigurationHandler $platformConfigHandler)
    {
        $this->platformConfigHandler = $platformConfigHandler;

        parent::__construct($registry, User::class);
    }

    public function search(string $search, int $nbResults)
    {
        return $this->createQueryBuilder('u')
            ->where('(UPPER(u.username) LIKE :search OR UPPER(u.firstName) LIKE :search OR UPPER(u.lastName) LIKE :search)')
            ->andWhere('u.isEnabled = true AND u.isRemoved = false')
            ->setFirstResult(0)
            ->setMaxResults($nbResults)
            ->setParameter('search', '%'.strtoupper($search).'%')
            ->getQuery()
            ->getResult();
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
     * @return User[]
     */
    public function findAll()
    {
        return $this->_em->createQuery('
            SELECT u, pws, g, r, rws
            FROM Claroline\\CoreBundle\\Entity\\User u
            LEFT JOIN u.personalWorkspace pws
            LEFT JOIN u.groups g
            LEFT JOIN u.roles r
            LEFT JOIN r.workspace rws
            WHERE u.isRemoved = false
        ')->getResult();
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
            SELECT u, r, g FROM Claroline\\CoreBundle\\Entity\\User u
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
     * @param string $order
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
            SELECT DISTINCT u 
            FROM Claroline\\CoreBundle\\Entity\\User u
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
     * Returns the users who are members of one of the given workspaces. Users's groups are not
     * taken into account.
     *
     * @param array $workspaces
     *
     * @return User[]
     */
    public function findByWorkspaces(array $workspaces)
    {
        $dql = '
            SELECT u
            FROM Claroline\CoreBundle\Entity\User u
            JOIN u.roles ur
            LEFT JOIN u.groups g
            LEFT JOIN g.roles gr
            LEFT JOIN gr.workspace grws
            LEFT JOIN ur.workspace uws
            WHERE (uws.id IN (:workspaces) OR grws.id IN (:workspaces))
            AND u.isRemoved = false
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspaces', $workspaces);

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
     * @param Role $role
     * @param $restrictionRoleNames
     * @param null $organizations
     *
     * @return int
     */
    public function countUsersByRole(Role $role, $restrictionRoleNames = null, $organizations = null)
    {
        $qb = $this->createQueryBuilder('user')
            ->select('COUNT(DISTINCT user.id)')
            ->leftJoin('user.roles', 'roles')
            ->andWhere('roles.id = :roleId')
            ->andWhere('user.isEnabled = :enabled')
            ->setParameter('roleId', $role->getId())
            ->setParameter('enabled', true);
        if (!empty($restrictionRoleNames)) {
            $qb->andWhere('user.id NOT IN (:userIds)')
                ->setParameter('userIds', $this->findUserIdsInRoles($restrictionRoleNames));
        }
        if (null !== $organizations) {
            $qb->join('user.userOrganizationReferences', 'orgaRef')
                ->andWhere('orgaRef.organization IN (:organizations)')
                ->setParameter('organizations', $organizations);
        }
        $query = $qb->getQuery();

        return $query->getSingleScalarResult();
    }

    public function countUsers(array $organizations = [])
    {
        $qb = $this->createQueryBuilder('user')
            ->select('COUNT(user.id)')
            ->where('user.isRemoved = false')
            ->andWhere('user.isEnabled = true');

        if (!empty($organizations)) {
            $qb->join('user.userOrganizationReferences', 'orgaRef')
                ->andWhere('orgaRef.organization IN (:organizations)')
                ->setParameter('organizations', $organizations);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
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
            ->select('DISTINCT(user.id) as id')
            ->leftJoin('user.roles', 'roles')
            ->andWhere('roles.name IN (:roleNames)')
            ->andWhere('user.isRemoved = false')
            ->setParameter('roleNames', $roleNames);
        $query = $qb->getQuery();

        return array_column($query->getScalarResult(), 'id');
    }

    /**
     * Returns the first name, last name, username and number of workspaces of
     * each user enrolled in at least one workspace.
     *
     * @param int $max
     *
     * @return User[]
     */
    public function findUsersEnrolledInMostWorkspaces($max, $organizations = null)
    {
        $orgasJoin = '';
        $orgasCondition = '';
        if (null !== $organizations) {
            $orgasJoin = 'JOIN ws.organizations orgas';
            $orgasCondition = 'AND orgas IN (:organizations)';
        }
        $dql = "
            SELECT CONCAT(CONCAT(u.firstName, ' '), u.lastName) AS name, u.username, COUNT(DISTINCT ws.id) AS total
            FROM Claroline\\CoreBundle\\Entity\\User u, Claroline\\CoreBundle\\Entity\\Workspace\\Workspace ws
            ${orgasJoin}
            WHERE (CONCAT(CONCAT(u.id,':'), ws.id) IN
            (
                SELECT CONCAT(CONCAT(u1.id, ':'), ws1.id)
                FROM Claroline\\CoreBundle\\Entity\\Workspace\\Workspace ws1
                JOIN ws1.roles r1
                JOIN r1.users u1
            ) OR CONCAT(CONCAT(u.id, ':'), ws.id) IN
            (
                SELECT CONCAT(CONCAT(u2.id, ':'), ws2.id)
                FROM Claroline\\CoreBundle\\Entity\\Workspace\\Workspace ws2
                JOIN ws2.roles r2
                JOIN r2.groups g2
                JOIN g2.users u2
            ))
            ${orgasCondition}
            AND u.isRemoved = false
            GROUP BY u.id
            ORDER BY total DESC, name ASC
        ";

        $query = $this->_em->createQuery($dql);

        if (null !== $organizations) {
            $query->setParameter('organizations', $organizations);
        }

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
        $dql = '
            SELECT u FROM Claroline\CoreBundle\Entity\User u
            JOIN u.roles r WHERE r IN (:roles) AND u.isRemoved = false
            ORDER BY u.lastName
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('roles', $roles);

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
    public function findUsersOwnersOfMostWorkspaces($max, $organizations = null)
    {
        $orgasJoin = '';
        $orgasCondition = '';
        if (null !== $organizations) {
            $orgasJoin = 'JOIN ws.organizations orgas';
            $orgasCondition = 'AND orgas IN (:organizations)';
        }

        $dql = "
            SELECT CONCAT(CONCAT(u.firstName,' '), u.lastName) AS name, u.username, COUNT(DISTINCT ws.id) AS total
            FROM Claroline\\CoreBundle\\Entity\\Workspace\\Workspace ws
            JOIN ws.creator u
            ${orgasJoin}
            WHERE u.isRemoved = false
            ${orgasCondition}
            GROUP BY u.id
            ORDER BY total DESC
        ";
        $query = $this->_em->createQuery($dql);

        if (null !== $organizations) {
            $query->setParameter('organizations', $organizations);
        }

        if ($max > 1) {
            $query->setMaxResults($max);
        }

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

    public function findUsersWithoutMainOrganization($count = false, $limit = -1, $offset = -1)
    {
        $selectUsr = $count ? 'COUNT(usr.id)' : 'usr';
        $query = $this->getEntityManager()
            ->createQuery('
                SELECT '.$selectUsr.' FROM Claroline\CoreBundle\Entity\User usr
                WHERE usr.id NOT IN (
                  SELECT IDENTITY(uo.user) FROM Claroline\CoreBundle\Entity\Organization\UserOrganizationReference uo
                  WHERE uo.isMain = :main
                )
            ')
            ->setParameter('main', true);
        if (!$count && $limit > 0 && $offset > -1) {
            $query->setMaxResults($limit)->setFirstResult($offset);
        }

        return $count ? $query->getSingleScalarResult() : $query->getResult();
    }
}
