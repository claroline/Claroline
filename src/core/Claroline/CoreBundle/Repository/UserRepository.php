<?php

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Group;
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
                sprintf('Unable to find an active admin AcmeUserBundle:User object identified by "%s".', $username)
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

        $dql = "SELECT u, groups, group_roles, roles, ws, pwu FROM Claroline\CoreBundle\Entity\User u
            LEFT JOIN u.groups groups
            LEFT JOIN groups.roles group_roles
            LEFT JOIN u.roles roles
            LEFT JOIN roles.workspace ws
            LEFT JOIN ws.personalUser pwu
            WHERE u.id = :userId";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('userId', $user->getId());
        $user = $query->getSingleResult();

        return $user;
    }

    /*
     * UserProviderInterface method
     */
    public function supportsClass($class)
    {
        return $this->getEntityName() === $class || is_subclass_of($class, $this->getEntityName());
    }

    /**
     * Returns the users whose registered in a workspace for a role (including groups roles).
     *
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param \Claroline\CoreBundle\Entity\Role $role
     *
     * @return array
     */
    public function findByWorkspaceAndRole(AbstractWorkspace $workspace, Role $role)
    {
        $dql = "
            SELECT DISTINCT u FROM Claroline\CoreBundle\Entity\User u
            LEFT JOIN u.roles wr WITH wr IN (
                SELECT pr from Claroline\CoreBundle\Entity\Role pr WHERE pr.type = ".Role::WS_ROLE."
            )
            LEFT JOIN wr.workspace w
            WHERE w.id = {$workspace->getId()}";

        if ($role != null) {
            $dql .= " AND wr.id = {$role->getId()}";
        }

        $query = $this->_em->createQuery($dql);
        $userResults = $query->getResult();

        $dql = "
            SELECT DISTINCT u FROM Claroline\CoreBundle\Entity\User u
            JOIN u.groups g
            JOIN g.roles wr WITH wr IN (
                SELECT pr from Claroline\CoreBundle\Entity\Role pr WHERE pr.type = ".Role::WS_ROLE."
            )
            LEFT JOIN wr.workspace w
            WHERE w.id = {$workspace->getId()}";

        if ($role != null) {
            $dql .= " AND wr.id = {$role->getId()}";
        }

        $query = $this->_em->createQuery($dql);
        $groupResults = $query->getResult();

        return array_merge($userResults, $groupResults);

    }

    public function findWorkspaceOutsidersByName(AbstractWorkspace $workspace, $search, $getQuery = false)
    {
        $upperSearch = strtoupper($search);

        $dql = "
            SELECT u, ws, r FROM Claroline\CoreBundle\Entity\User u
            LEFT JOIN u.personalWorkspace ws
            LEFT JOIN u.roles r
            WITH r IN (SELECT pr from Claroline\CoreBundle\Entity\Role pr WHERE pr.type = ".Role::WS_ROLE.")
            WHERE u NOT IN
            (
                SELECT us FROM Claroline\CoreBundle\Entity\User us
                LEFT JOIN us.roles wr WITH wr IN (
                    SELECT pr2 from Claroline\CoreBundle\Entity\Role pr2 WHERE pr2.type = ".Role::WS_ROLE."
                )
                LEFT JOIN wr.workspace w
                WHERE w.id = :id
            )
            AND ( UPPER(u.firstName) LIKE :search
                OR UPPER(u.lastName) LIKE :search
                OR UPPER(u.username) LIKE :search
            )
        ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('id', $workspace->getId())
            ->setParameter('search', "%{$upperSearch}%");

        return ($getQuery) ? $query: $query->getResult();
    }

    public function findWorkspaceOutsiders(AbstractWorkspace $workspace, $getQuery = false)
    {
        $dql = "
            SELECT u, ws, r FROM Claroline\CoreBundle\Entity\User u
            LEFT JOIN u.personalWorkspace ws
            LEFT JOIN u.roles r
            WITH r IN (SELECT pr from Claroline\CoreBundle\Entity\Role pr WHERE pr.type = ".Role::WS_ROLE.")
            WHERE u NOT IN
            (
                SELECT us FROM Claroline\CoreBundle\Entity\User us
                LEFT JOIN us.roles wr WITH wr IN (
                    SELECT pr2 from Claroline\CoreBundle\Entity\Role pr2 WHERE pr2.type = ".Role::WS_ROLE."
                )
                LEFT JOIN wr.workspace w
                WHERE w.id = :id
            )
        ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('id', $workspace->getId());

        return ($getQuery) ? $query: $query->getResult();
    }

    public function findAll($getQuery = false)
    {
        if ($getQuery) {
            $dql = 'SELECT u, r, pws from Claroline\CoreBundle\Entity\User u
                        JOIN u.roles r WITH r IN (
                        SELECT pr from Claroline\CoreBundle\Entity\Role pr WHERE pr.type = '.Role::BASE_ROLE.'
                        )
                        JOIN u.personalWorkspace pws';

            //the join on role is required because this method is only fired in the administration
            //and we only want the platform roles of a user.

            return $this->_em->createQuery($dql);

        }

        return parent::findAll();
    }

    /**
     * Search users whose firstname, lastname or username
     * match $search.
     *
     * @param string $search
     * @param boolean $getQuery
     */
    public function findByName($search, $getQuery = false)
    {
        $upperSearch = strtoupper($search);
        $upperSearch = trim($upperSearch);
        $upperSearch = preg_replace('/\s+/', ' ', $upperSearch);

        $dql = "
            SELECT u, r, pws FROM Claroline\CoreBundle\Entity\User u
            JOIN u.roles r
            JOIN u.personalWorkspace pws
            WHERE UPPER(u.lastName) LIKE :search
            OR UPPER(u.firstName) LIKE :search
            OR UPPER(u.username) LIKE :search
            OR CONCAT(UPPER(u.firstName), ' ', UPPER(u.lastName)) LIKE :search
            OR CONCAT(UPPER(u.lastName), ' ', UPPER(u.firstName)) LIKE :search
        ";

        $query = $this->_em->createQuery($dql)
              ->setParameter('search', "%{$upperSearch}%");

        return ($getQuery) ? $query: $query->getResult();
    }

    public function findByGroup(Group $group, $getQuery = false)
    {
        $dql = "
            SELECT DISTINCT u, g, pw, r from Claroline\CoreBundle\Entity\User u
            JOIN u.groups g
            JOIN u.personalWorkspace pw
            JOIN u.roles r WITH r IN (
                SELECT pr from Claroline\CoreBundle\Entity\Role pr WHERE pr.type = ".Role::BASE_ROLE."
            )
            WHERE g.id = :groupId ORDER BY u.id";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('groupId', $group->getId());

        return ($getQuery) ? $query: $query->getResult();
    }

    public function findByNameAndGroup($search, Group $group, $getQuery = false)
    {
        $upperSearch = strtoupper($search);

        $dql = "
            SELECT DISTINCT u, g, pw, r from Claroline\CoreBundle\Entity\User u
            JOIN u.groups g
            JOIN u.personalWorkspace pw
            JOIN u.roles r WITH r IN (
                SELECT pr from Claroline\CoreBundle\Entity\Role pr WHERE pr.type = ".Role::BASE_ROLE."
            )
            WHERE g.id = :groupId
            AND (UPPER(u.username) LIKE :search
            OR UPPER(u.lastName) LIKE :search
            OR UPPER(u.firstName) LIKE :search)
            ORDER BY u.id
        ";

        $query = $this->_em->createQuery($dql)
            ->setParameter('search', "%{$upperSearch}%")
            ->setParameter('groupId', $group->getId());

        return ($getQuery) ? $query: $query->getResult();
    }

    public function findByWorkspace(AbstractWorkspace $workspace, $getQuery = false)
    {
        $dql = "
            SELECT wr, u, ws from Claroline\CoreBundle\Entity\User u
            JOIN u.roles wr WITH wr IN (
                SELECT pr from Claroline\CoreBundle\Entity\Role pr WHERE pr.type = ".Role::WS_ROLE."
            )
            LEFT JOIN wr.workspace w
            JOIN u.personalWorkspace ws
            WHERE w.id = :workspaceId";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspaceId', $workspace->getId());

        return ($getQuery) ? $query: $query->getResult();
    }

    public function findByWorkspaceAndName(AbstractWorkspace $workspace, $search, $getQuery = false)
    {
        $upperSearch = strtoupper($search);

        $dql = "
            SELECT u, r, ws FROM Claroline\CoreBundle\Entity\User u
            JOIN u.roles r WITH r IN (
                SELECT pr from Claroline\CoreBundle\Entity\Role pr WHERE pr.type = ".Role::WS_ROLE."
            )
            LEFT JOIN r.workspace wol
            JOIN u.personalWorkspace ws
            WHERE wol.id = :workspaceId AND u IN (SELECT us FROM Claroline\CoreBundle\Entity\User us WHERE
            UPPER(us.lastName) LIKE :search
            OR UPPER(us.firstName) LIKE :search
            OR UPPER(us.username) LIKE :search
            )
        ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspaceId', $workspace->getId())
              ->setParameter('search', "%{$upperSearch}%");

        return ($getQuery) ? $query: $query->getResult();
    }

    /**
     * Find users who are not registered in the group $group.
     *
     * @param \Claroline\CoreBundle\Entity\Group $group
     * @param integer $offset
     * @param integer $limit
     *
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     */
    public function findGroupOutsiders(Group $group, $getQuery = false)
    {
        $dql = "
            SELECT DISTINCT u, ws, r FROM Claroline\CoreBundle\Entity\User u
            JOIN u.personalWorkspace ws
            JOIN u.roles r WITH r IN (
                SELECT pr from Claroline\CoreBundle\Entity\Role pr WHERE pr.type = ".Role::BASE_ROLE."
            )
            WHERE u NOT IN (
                SELECT us FROM Claroline\CoreBundle\Entity\User us
                JOIN us.groups gs
                WHERE gs.id = :groupId
            ) ORDER BY u.id
        ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('groupId', $group->getId());

        return ($getQuery) ? $query: $query->getResult();
    }

    public function findGroupOutsidersByName(Group $group, $search, $getQuery = false)
    {
        $search = strtoupper($search);

        $dql = "
            SELECT DISTINCT u, ws, r FROM Claroline\CoreBundle\Entity\User u
            JOIN u.personalWorkspace ws
            JOIN u.roles r WITH r IN (
                SELECT pr from Claroline\CoreBundle\Entity\Role pr WHERE pr.type = ".Role::BASE_ROLE."
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
        $query->setParameter('groupId', $group->getId())
              ->setParameter('search', "%{$search}%");

        return ($getQuery) ? $query: $query->getResult();
    }

    public function findAllExcept(User $excludedUser)
    {
        $dql = '
            SELECT u FROM Claroline\CoreBundle\Entity\User u
            WHERE u.id <> :userId
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('userId', $excludedUser->getId());

        return $query->getResult();
    }

    public function count()
    {
        $dql = "SELECT COUNT(u) FROM Claroline\CoreBundle\Entity\User u";
        $query = $this->_em->createQuery($dql);

        return $query->getSingleScalarResult();
    }

    /**
     * extractQuery
     *
     * @param array $params
     * @return Query
     */
    public function extractQuery($params)
    {
        $search = $params['search'];
        if ($search !== null) {

            return $this->findByNameQuery($search, 0, 10);
        }

        return null;
    }

    /**
     * extract
     *
     * @param array $params
     * @return DoctrineCollection
     */
    public function extract($params)
    {
        $query = $this->extractQuery($params);

        return is_null($query) ? array() : $query->getResult();
    }
}