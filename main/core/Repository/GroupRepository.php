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
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;

class GroupRepository extends EntityRepository
{
    /**
     * Returns the groups which are not member of a workspace.
     *
     * @param Workspace $workspace
     * @param bool      $executeQuery
     *
     * @return array[Group]|Query
     */
    public function findWorkspaceOutsiders(Workspace $workspace, $executeQuery = true)
    {
        $dql = '
            SELECT g, r FROM Claroline\CoreBundle\Entity\Group g
            LEFT JOIN g.roles r
            WHERE g NOT IN
            (
                SELECT gr FROM Claroline\CoreBundle\Entity\Group gr
                LEFT JOIN gr.roles wr WITH wr IN (
                    SELECT pr from Claroline\CoreBundle\Entity\Role pr WHERE pr.type = :type
                )
                JOIN wr.workspace w
                WHERE w.id = :id
            )
            ORDER BY g.id
       ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('id', $workspace->getId());
        $query->setParameter('type', Role::WS_ROLE);

        return $executeQuery ? $query->getResult() : $query;
    }

    /**
     * Returns the groups which are not member of a workspace, filtered by a search on
     * their name.
     *
     * @param Workspace $workspace
     * @param string    $search
     * @param bool      $executeQuery
     *
     * @return array[Group]|Query
     */
    public function findWorkspaceOutsidersByName(Workspace $workspace, $search, $executeQuery = true)
    {
        $dql = '
            SELECT g, r FROM Claroline\CoreBundle\Entity\Group g
            LEFT JOIN g.roles r
            WHERE UPPER(g.name) LIKE :search
            AND g NOT IN
            (
                SELECT gr FROM Claroline\CoreBundle\Entity\Group gr
                JOIN gr.roles wr WITH wr IN (
                    SELECT pr from Claroline\CoreBundle\Entity\Role pr WHERE pr.type = :type
                )
                JOIN wr.workspace w
                WHERE w.id = :id
            )
            ORDER BY g.id
        ';
        $search = strtoupper($search);
        $query = $this->_em->createQuery($dql);
        $query->setParameter('id', $workspace->getId());
        $query->setParameter('search', "%{$search}%");
        $query->setParameter('type', Role::WS_ROLE);

        return $executeQuery ? $query->getResult() : $query;
    }

    /**
     * Returns the groups which are member of a workspace.
     *
     * @param Workspace $workspace
     * @param bool      $executeQuery
     *
     * @return array[Group]|Query
     */
    public function findByWorkspace(Workspace $workspace, $executeQuery = true)
    {
        $dql = '
            SELECT g, wr
            FROM Claroline\CoreBundle\Entity\Group g
            LEFT JOIN g.roles wr WITH wr IN (
                SELECT pr from Claroline\CoreBundle\Entity\Role pr WHERE pr.type = :type
            )
            LEFT JOIN wr.workspace w
            WHERE w.id = :workspaceId
       ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspaceId', $workspace->getId());
        $query->setParameter('type', Role::WS_ROLE);

        return $executeQuery ? $query->getResult() : $query;
    }

    /**
     * Returns the groups which are member of a workspace.
     *
     * @param array $workspace
     * @param bool  $executeQuery
     *
     * @return array[Group]|Query
     */
    public function findGroupsByWorkspaces(array $workspaces, $executeQuery = true)
    {
        $dql = '
            SELECT g
            FROM Claroline\CoreBundle\Entity\Group g
            LEFT JOIN g.roles wr WITH wr IN (
                SELECT pr from Claroline\CoreBundle\Entity\Role pr WHERE pr.type = :type
            )
            LEFT JOIN wr.workspace w
            WHERE w IN (:workspaces)
            ORDER BY g.name
       ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspaces', $workspaces);
        $query->setParameter('type', Role::WS_ROLE);

        return $executeQuery ? $query->getResult() : $query;
    }

    /**
     * Returns the groups which are member of a workspace
     * and whose name corresponds the search.
     *
     * @param array  $workspace
     * @param string $search
     *
     * @return array[Group]
     */
    public function findGroupsByWorkspacesAndSearch(array $workspaces, $search)
    {
        $upperSearch = strtoupper(trim($search));
        $dql = '
            SELECT g
            FROM Claroline\CoreBundle\Entity\Group g
            LEFT JOIN g.roles wr WITH wr IN (
                SELECT pr
                FROM Claroline\CoreBundle\Entity\Role pr
                WHERE pr.type = :type
            )
            LEFT JOIN wr.workspace w
            WHERE w IN (:workspaces)
            AND UPPER(g.name) LIKE :search
            ORDER BY g.name
       ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspaces', $workspaces);
        $query->setParameter('search', "%{$upperSearch}%");
        $query->setParameter('type', Role::WS_ROLE);

        return $query->getResult();
    }

    /**
     * Returns the groups which are member of a workspace, filtered by a search on
     * their name.
     *
     * @param Workspace $workspace
     * @param string    $search
     * @param bool      $executeQuery
     *
     * @return array[Group]|Query
     */
    public function findByWorkspaceAndName(Workspace $workspace, $search, $executeQuery = true)
    {
        $dql = '
            SELECT g, r FROM Claroline\CoreBundle\Entity\Group g
            LEFT JOIN g.roles r
            WHERE UPPER(g.name) LIKE :search
            AND g IN
            (
                SELECT gr FROM Claroline\CoreBundle\Entity\Group gr
                JOIN gr.roles wr WITH wr IN (
                    SELECT pr from Claroline\CoreBundle\Entity\Role pr WHERE pr.type = :type
                )
                JOIN wr.workspace w
                WHERE w.id = :id
            )
            ORDER BY g.id
        ';
        $search = strtoupper($search);
        $query = $this->_em->createQuery($dql);
        $query->setParameter('id', $workspace->getId());
        $query->setParameter('search', "%{$search}%");
        $query->setParameter('type', Role::WS_ROLE);

        return $executeQuery ? $query->getResult() : $query;
    }

    /**
     * Returns all the groups.
     *
     * @param bool   $executeQuery
     * @param string $orderedBy
     *
     * @return array[Group]|Query
     */
    public function findAll($executeQuery = true, $orderedBy = 'id', $order = null)
    {
        if (!$executeQuery) {
            return $this->_em->createQuery(
                "SELECT g, r, ws FROM Claroline\CoreBundle\Entity\Group g
                 LEFT JOIN g.roles r
                 LEFT JOIN r.workspace ws
                 ORDER BY g.{$orderedBy} {$order}"
            );
        }

        return parent::findAll();
    }

    /**
     * Returns all the groups by search.
     *
     * @param string $search
     *
     * @return array[Group]
     */
    public function findAllGroupsBySearch($search)
    {
        $upperSearch = strtoupper(trim($search));

        if ($search !== '') {
            $dql = '
                SELECT g
                FROM Claroline\CoreBundle\Entity\Group g
                WHERE UPPER(g.name) LIKE :search
            ';

            $query = $this->_em->createQuery($dql);
            $query->setParameter('search', "%{$upperSearch}%");

            return $query->getResult();
        }

        return parent::findAll();
    }

    /**
     * Returns all the groups whose name match a search string.
     *
     * @param string $search
     * @param bool   $executeQuery
     * @param string $orderedBy
     * @param string $order        ( ascending , descending )
     *
     * @return \Claroline\CoreBundle\Entity\Group[]|Query
     */
    public function findByName($search, $executeQuery = true, $orderedBy = 'id', $order = null)
    {
        $dql = "
            SELECT g, r, ws
            FROM Claroline\CoreBundle\Entity\Group g
            LEFT JOIN g.roles r
            LEFT JOIN r.workspace ws
            WHERE UPPER(g.name) LIKE :search
            ORDER BY g.{$orderedBy}
            ".$order
        ;
        $search = strtoupper($search);
        $query = $this->_em->createQuery($dql);
        $query->setParameter('search', "%{$search}%");

        return $executeQuery ? $query->getResult() : $query;
    }

    /**
     * @param string $search
     *
     * @return array
     */
    public function findByNameForAjax($search)
    {
        $resultArray = [];
        $groups = $this->findByName($search);

        foreach ($groups as $group) {
            $resultArray[] = [
                'id' => $group->getId(),
                'text' => $group->getName(),
            ];
        }

        return $resultArray;
    }

    /**
     * @param array $params
     *
     * @return ArrayCollection
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

        return [];
    }

    public function findByRoles(array $roles, $getQuery = false, $orderedBy = 'id', $order = null)
    {
        $dql = "
            SELECT u, ws, r FROM Claroline\CoreBundle\Entity\Group u
            JOIN u.roles r
            LEFT JOIN r.workspace ws
            WHERE r IN (:roles)
            ORDER BY u.{$orderedBy}
            ".$order;

        $query = $this->_em->createQuery($dql);
        $query->setParameter('roles', $roles);

        return ($getQuery) ? $query : $query->getResult();
    }

    public function findByRolesAndName(array $roles, $name, $getQuery = false, $orderedBy = 'id')
    {
        $search = strtoupper($name);
        $dql = "
            SELECT u, ws, r FROM Claroline\CoreBundle\Entity\Group u
            JOIN u.roles r
            LEFT JOIN r.workspace ws
            WHERE r IN (:roles)
            AND UPPER(u.name) LIKE :search
            ORDER BY u.{$orderedBy}
            ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('roles', $roles);
        $query->setParameter('search', "%{$search}%");

        return ($getQuery) ? $query : $query->getResult();
    }

    /**
     * This method should be renamed.
     * Find groups who are outside the workspace and users whose role are in $roles.
     */
    public function findOutsidersByWorkspaceRoles(array $roles, Workspace $workspace, $getQuery = false)
    {
        //feel free to make this request easier if you can

        $dql = "
            SELECT u FROM Claroline\CoreBundle\Entity\Group u
            WHERE u NOT IN (
                SELECT u2 FROM Claroline\CoreBundle\Entity\Group u2
                JOIN u2.roles r WHERE r IN (:roles) AND
                u2 NOT IN (
                    SELECT u3 FROM Claroline\CoreBundle\Entity\Group u3
                    JOIN u3.roles r2
                    JOIN r2.workspace ws
                    WHERE r2 NOT IN (:roles)
                    AND ws = :wsId
                )
            )
            ORDER BY u.name
            ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('roles', $roles);
        $query->setParameter('wsId', $workspace);

        return $getQuery ? $query : $query->getResult();
    }

    /**
     * This method should be renamed.
     * Find groups who are outside the workspace and users whose role are in $roles.
     */
    public function findOutsidersByWorkspaceRolesAndName(
        array $roles,
        $name,
        Workspace $workspace,
        $getQuery = false
    ) {
        //feel free to make this request easier if you can
        $search = strtoupper($name);

        $dql = "
            SELECT u FROM Claroline\CoreBundle\Entity\Group u
            WHERE u NOT IN (
                SELECT u2 FROM Claroline\CoreBundle\Entity\Group u2
                JOIN u2.roles r WHERE r IN (:roles) AND
                u2 NOT IN (
                    SELECT u3 FROM Claroline\CoreBundle\Entity\Group u3
                    JOIN u3.roles r2
                    JOIN r2.workspace ws
                    WHERE r2 NOT IN (:roles)
                    AND ws = :wsId
                )
            )
            AND UPPER(u.name) LIKE :search
            ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('roles', $roles);
        $query->setParameter('wsId', $workspace);
        $query->setParameter('search', "%{$search}%");

        return $getQuery ? $query : $query->getResult();
    }

    /**
     * Returns groups by their names.
     *
     * @param array $names
     *
     * @return array[Group]
     *
     * @throws MissingObjectException if one or more groups cannot be found
     */
    public function findGroupsByNames(array $names)
    {
        $nameCount = count($names);
        $dql = '
            SELECT g FROM Claroline\CoreBundle\Entity\Group g
            WHERE g.name IN (:names)
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('names', $names);

        $result = $query->getResult();

        if (($groupCount = count($result)) !== $nameCount) {
            throw new MissingObjectException("{$groupCount} out of {$nameCount} groups were found");
        }

        return $result;
    }

    public function findNames()
    {
        $dql = 'SELECT g.name as name FROM Claroline\CoreBundle\Entity\Group g';
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    /**
     * Returns a group by its name.
     *
     * @param string $name
     * @param bool   $executeQuery
     *
     * @return Group|null
     */
    public function findGroupByName($name, $executeQuery = true)
    {
        $dql = '
            SELECT g
            FROM Claroline\CoreBundle\Entity\Group g
            WHERE g.name = :name
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('name', $name);

        return $executeQuery ? $query->getOneOrNullResult() : $query;
    }

    public function findAllGroups($orderedBy = 'id', $order = 'ASC', $executeQuery = true)
    {
        $dql = "
            SELECT g
            FROM Claroline\CoreBundle\Entity\Group g
            ORDER BY g.{$orderedBy} {$order}
        ";

        $query = $this->_em->createQuery($dql);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function countGroupsByRole(Role $role)
    {
        $qb = $this->createQueryBuilder('grp')
            ->select('COUNT(DISTINCT grp.id)')
            ->leftJoin('grp.roles', 'roles')
            ->andWhere('roles.id = :roleId')
            ->setParameter('roleId', $role->getId());
        $query = $qb->getQuery();

        return $query->getSingleScalarResult();
    }
}
