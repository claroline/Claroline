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
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserRepository extends EntityRepository implements UserProviderInterface, UserLoaderInterface, PasswordUpgraderInterface
{
    public function search(string $search, int $nbResults)
    {
        return $this->createQueryBuilder('u')
            ->where('(UPPER(u.username) LIKE :search OR UPPER(u.firstName) LIKE :search OR UPPER(u.lastName) LIKE :search)')
            ->andWhere('u.disabled = false AND u.isRemoved = false')
            ->setFirstResult(0)
            ->setMaxResults($nbResults)
            ->setParameter('search', '%'.strtoupper($search).'%')
            ->getQuery()
            ->getResult();
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $query = $this->getEntityManager()
            ->createQuery('
                SELECT u FROM Claroline\CoreBundle\Entity\User u
                WHERE u.username LIKE :username
                OR u.email LIKE :username
            ')
            ->setParameter('username', $identifier);

        try {
            $user = $query->getSingleResult();
        } catch (NoResultException $e) {
            throw new UserNotFoundException(sprintf('Unable to find an active user identified by "%s".', $identifier));
        }

        return $user;
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        $em = $this->getEntityManager();
        $user->setPassword($newHashedPassword);

        $em->persist($user);
        $em->flush();
    }

    public function refreshUser(UserInterface $user): User
    {
        return $this->getEntityManager()
            ->createQuery('
                SELECT u, ur, g, gr, uo, o 
                FROM Claroline\CoreBundle\Entity\User u
                LEFT JOIN u.userOrganizationReferences AS uo
                LEFT JOIN uo.organization AS o
                LEFT JOIN u.roles AS ur
                LEFT JOIN u.groups AS g
                LEFT JOIN g.roles AS gr
                WHERE u.id = :id
            ')
            ->setParameter('id', $user->getId())
            ->getSingleResult();
    }

    public function supportsClass($class): bool
    {
        return $this->getEntityName() === $class || is_subclass_of($class, $this->getEntityName());
    }

    /**
     * Returns the users of a group.
     *
     * @return User[]
     */
    public function findByGroup(Group $group): array
    {
        $query = $this->getEntityManager()->createQuery('
            SELECT DISTINCT u 
            FROM Claroline\CoreBundle\Entity\User u
            JOIN u.groups g
            WHERE g.id = :groupId
              AND u.isRemoved = false
              AND u.disabled = false
              AND u.technical = false
        ');

        $query->setParameter('groupId', $group->getId());

        return $query->getResult();
    }

    /**
     * Returns the users who are members of one of the given workspaces.
     *
     * @return User[]
     */
    public function findByWorkspaces(array $workspaces): array
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
              AND u.disabled = false
              AND u.technical = false
        ';
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('workspaces', $workspaces);

        return $query->getResult();
    }

    /**
     * Returns users by their usernames.
     *
     * @return User[]
     */
    public function findByUsernames(array $usernames): array
    {
        if (empty($usernames)) {
            return [];
        }

        return $this->getEntityManager()
            ->createQuery('
                SELECT u FROM Claroline\CoreBundle\Entity\User u
                WHERE u.isRemoved = false
                  AND u.disabled = false
                  AND u.username IN (:usernames)
            ')
            ->setParameter('usernames', $usernames)
            ->getResult();
    }

    public function countUsers(array $organizations = []): int
    {
        $qb = $this->createQueryBuilder('user')
            ->select('COUNT(DISTINCT user.id)')
            ->where('user.isRemoved = false')
            ->andWhere('user.disabled = false')
            ->andWhere('user.technical = false');

        if (!empty($organizations)) {
            $qb->join('user.userOrganizationReferences', 'orgaRef')
                ->andWhere('orgaRef.organization IN (:organizations)')
                ->setParameter('organizations', $organizations);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @return User[]
     */
    public function findByRoles(array $roles, bool $includeGroups = true): iterable
    {
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata(User::class, 'u');

        $query = '
            SELECT u.* 
            FROM claro_user AS u
            LEFT JOIN claro_user_role AS ur ON (u.id = ur.user_id)
            WHERE (ur.role_id IN (:roles)) 
              AND u.is_removed = false 
              AND u.disabled = false
              AND u.technical = false
        ';

        if ($includeGroups) {
            $query = "($query) UNION DISTINCT (
                SELECT u.* 
                FROM claro_user AS u
                LEFT JOIN claro_user_group AS ug ON (u.id = ug.user_id)
                LEFT JOIN claro_group_role AS gr ON (ug.group_id = gr.group_id)
                WHERE (gr.role_id IN (:roles)) 
                AND u.is_removed = false 
                AND u.disabled = false
                AND u.technical = false
            )";
        }

        return $this->getEntityManager()
            ->createNativeQuery($query, $rsm)
            ->setParameter('roles', array_map(function (Role $role) {
                return $role->getId();
            }, $roles))
            ->getResult();
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
