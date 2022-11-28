<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CommunityBundle\Repository;

use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserRepository extends ServiceEntityRepository implements UserProviderInterface, UserLoaderInterface, PasswordUpgraderInterface
{
    /** @var PlatformConfigurationHandler */
    private $platformConfigHandler;

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
              AND u.technical = false
        ')->getResult();
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
              AND u.technical = false
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
            AND u.technical = false
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
            ->andWhere('user.technical = false')
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
            ->andWhere('user.isEnabled = true')
            ->andWhere('user.technical = false');

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
            ->andWhere('user.technical = false')
            ->setParameter('roleNames', $roleNames);
        $query = $qb->getQuery();

        return array_column($query->getScalarResult(), 'id');
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
                    AND u.technical = false
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
                    AND u.technical = false
                )
            ', $rsm)
            ->setParameter('roles', array_map(function (Role $role) {
                return $role->getId();
            }, $roles))
            ->getResult();
    }

    public function findAllEnabledUsers($executeQuery = true)
    {
        $dql = '
            SELECT u
            FROM Claroline\CoreBundle\Entity\User u
            WHERE u.isEnabled = TRUE
              AND u.isRemoved = FALSE
              AND u.technical = false
        ';
        $query = $this->_em->createQuery($dql);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findInactiveSince($dateLastActivity)
    {
        return $this->createQueryBuilder('u')
            ->where('(u.lastActivity IS NULL OR u.lastActivity < :dateLastActivity)')
            ->andWhere('u.isEnabled = true AND u.isRemoved = false AND u.technical = false')
            ->setParameter('dateLastActivity', $dateLastActivity)
            ->getQuery()
            ->getResult();
    }
}
