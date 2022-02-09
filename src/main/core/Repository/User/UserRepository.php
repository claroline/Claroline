<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Repository\User;

use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Class UserRepository.
 */
class UserRepository extends ServiceEntityRepository implements UserProviderInterface, UserLoaderInterface, PasswordUpgraderInterface
{
    /** @var PlatformConfigurationHandler */
    private $platformConfigHandler;

    /**
     * UserRepository constructor.
     */
    public function __construct(ManagerRegistry $registry, PlatformConfigurationHandler $platformConfigHandler)
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

        $query = $this->_em->createQuery($dql);
        $query->setParameter('username', $username);

        try {
            $user = $query->getSingleResult();
        } catch (NoResultException $e) {
            throw new UsernameNotFoundException(sprintf('Unable to find an active user identified by "%s".', $username));
        }

        return $user;
    }

    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        $em = $this->getEntityManager();
        $user->setPassword($newEncodedPassword);

        $em->persist($user);
        $em->flush();
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
     * @return User[]
     */
    public function findByGroup(Group $group)
    {
        $query = $this->_em->createQuery('
            SELECT DISTINCT u 
            FROM Claroline\\CoreBundle\\Entity\\User u
            JOIN u.groups g
            WHERE g.id = :groupId
              AND u.isRemoved = false
              AND u.isEnabled = true
        ');

        $query->setParameter('groupId', $group->getId());

        return $query->getResult();
    }

    /**
     * Returns the users who are members of one of the given workspaces. Users's groups are not
     * taken into account.
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
     * @param $restrictionRoleNames
     * @param null $organizations
     *
     * @return int
     */
    public function countUsersByRole(Role $role, $restrictionRoleNames = null, $organizations = null, $dateCreated = null)
    {
        $qb = $this->createQueryBuilder('user')
            ->select('COUNT(DISTINCT user.id)')
            ->leftJoin('user.roles', 'roles')
            ->andWhere('roles.id = :roleId')
            ->andWhere('user.isEnabled = :enabled')
            ->andWhere('user.isRemoved = false')
            ->setParameter('roleId', $role->getId())
            ->setParameter('enabled', true);
        if (!empty($restrictionRoleNames)) {
            $qb->andWhere('user.id NOT IN (:userIds)')
                ->setParameter('userIds', $this->findUserIdsInRoles($restrictionRoleNames));
        }

        if ($dateCreated) {
            $qb
                ->andWhere('user.created <= :date')
                ->setParameter('date', $dateCreated);
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
    private function findUserIdsInRoles($roleNames)
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
     * @return User[]
     */
    public function findByRoles(array $roles)
    {
        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addRootEntityFromClassMetadata(User::class, 'u');

        return $this->_em
            ->createNativeQuery('
                (
                    SELECT u.* 
                    FROM claro_user AS u
                    LEFT JOIN claro_user_role AS ur ON (u.id = ur.user_id)
                    WHERE (ur.role_id IN (:roles)) 
                    AND u.is_removed = false 
                    AND u.is_enabled = true
                )
                UNION DISTINCT
                (
                    SELECT u.* 
                    FROM claro_user AS u
                    LEFT JOIN claro_user_group AS ug ON (u.id = ug.user_id)
                    LEFT JOIN claro_group_role AS gr ON (ug.group_id = gr.group_id)
                    WHERE (gr.role_id IN (:roles)) 
                    AND u.is_removed = false 
                    AND u.is_enabled = true
                )
            ', $rsm)
            ->setParameter('roles', array_map(function (Role $role) {
                return $role->getId();
            }, $roles))
            ->getResult();
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

    public function findAllEnabledUsers($executeQuery = true)
    {
        $dql = '
            SELECT u
            FROM Claroline\CoreBundle\Entity\User u
            WHERE u.isEnabled = TRUE
              AND u.isRemoved = FALSE
        ';
        $query = $this->_em->createQuery($dql);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findInactiveSince($dateLastActivity)
    {
        return $this->createQueryBuilder('u')
            ->where('(u.lastActivity IS NULL OR u.lastActivity < :dateLastActivity)')
            ->andWhere('u.isEnabled = true AND u.isRemoved = false')
            ->setParameter('dateLastActivity', $dateLastActivity)
            ->getQuery()
            ->getResult();
    }
}
