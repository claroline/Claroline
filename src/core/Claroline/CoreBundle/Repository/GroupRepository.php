<?php

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Role;

class GroupRepository extends EntityRepository
{
    /**
     * Returns the groups which are not member of a workspace.
     *
     * @param AbstractWorkspace $workspace
     * @param boolean           $executeQuery
     *
     * @return array[Group]|Query
     */
    public function findWorkspaceOutsiders(AbstractWorkspace $workspace, $executeQuery = true)
    {
        $dql = '
            SELECT g, r FROM Claroline\CoreBundle\Entity\Group g
            LEFT JOIN g.roles r
            WHERE g NOT IN
            (
                SELECT gr FROM Claroline\CoreBundle\Entity\Group gr
                LEFT JOIN gr.roles wr WITH wr IN (
                    SELECT pr from Claroline\CoreBundle\Entity\Role pr WHERE pr.type = ' . Role::WS_ROLE . '
                )
                JOIN wr.workspace w
                WHERE w.id = :id
            )
            ORDER BY g.id
       ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('id', $workspace->getId());

        return $executeQuery ? $query->getResult() : $query;
    }

    /**
     * Returns the groups which are not member of a workspace, filtered by a search on
     * their name.
     *
     * @param AbstractWorkspace $workspace
     * @param string            $search
     * @param boolean           $executeQuery
     *
     * @return array[Group]|Query
     */
    public function findWorkspaceOutsidersByName(AbstractWorkspace $workspace, $search, $executeQuery = true)
    {
        $dql = '
            SELECT g, r FROM Claroline\CoreBundle\Entity\Group g
            LEFT JOIN g.roles r
            WHERE UPPER(g.name) LIKE :search
            AND g NOT IN
            (
                SELECT gr FROM Claroline\CoreBundle\Entity\Group gr
                JOIN gr.roles wr WITH wr IN (
                    SELECT pr from Claroline\CoreBundle\Entity\Role pr WHERE pr.type = ' . Role::WS_ROLE . '
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

        return $executeQuery ? $query->getResult() : $query;
    }

    /**
     * Returns the groups which are member of a workspace.
     *
     * @param AbstractWorkspace $workspace
     * @param boolean           $executeQuery
     *
     * @return array[Group]|Query
     */
    public function findByWorkspace(AbstractWorkspace $workspace, $executeQuery = true)
    {
        $dql = '
            SELECT g, wr
            FROM Claroline\CoreBundle\Entity\Group g
            LEFT JOIN g.roles wr WITH wr IN (
                SELECT pr from Claroline\CoreBundle\Entity\Role pr WHERE pr.type = ' . Role::WS_ROLE . '
            )
            LEFT JOIN wr.workspace w
            WHERE w.id = :workspaceId
       ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspaceId', $workspace->getId());

        return $executeQuery ? $query->getResult() : $query;
    }

    /**
     * Returns the groups which are member of a workspace, filtered by a search on
     * their name.
     *
     * @param AbstractWorkspace $workspace
     * @param string            $search
     * @param boolean           $executeQuery
     *
     * @return array[Group]|Query
     */
    public function findByWorkspaceAndName(AbstractWorkspace $workspace, $search, $executeQuery = true)
    {
        $dql = '
            SELECT g, r FROM Claroline\CoreBundle\Entity\Group g
            LEFT JOIN g.roles r
            WHERE UPPER(g.name) LIKE :search
            AND g IN
            (
                SELECT gr FROM Claroline\CoreBundle\Entity\Group gr
                JOIN gr.roles wr WITH wr IN (
                    SELECT pr from Claroline\CoreBundle\Entity\Role pr WHERE pr.type = ' . Role::WS_ROLE . '
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

        return $executeQuery ? $query->getResult() : $query;
    }

    /**
     * Returns all the groups.
     *
     * @param boolean $executeQuery
     *
     * @return array[Group]|Query
     */
    public function findAll($executeQuery = true)
    {
        if (!$executeQuery) {
            return $this->_em->createQuery(
                'SELECT g, r FROM Claroline\CoreBundle\Entity\Group g
                 LEFT JOIN g.roles r'
            );
        }

        return parent::findAll();
    }

    /**
     * Returns all the groups whose name match a search string.
     *
     * @param string  $search
     * @param boolean $executeQuery
     *
     * @return array[Group]|Query
     */
    public function findByName($search, $executeQuery = true)
    {
        $dql = '
            SELECT g, r
            FROM Claroline\CoreBundle\Entity\Group g
            LEFT JOIN g.roles r
            WHERE UPPER(g.name) LIKE :search
        ';
        $search = strtoupper($search);
        $query = $this->_em->createQuery($dql);
        $query->setParameter('search', "%{$search}%");

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findByRoles(array $roles, $getQuery = false)
    {
        $dql = "
            SELECT u FROM Claroline\CoreBundle\Entity\Group u
            JOIN u.roles r WHERE r IN (:roles)
            ORDER BY u.name
            ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('roles', $roles);

        return ($getQuery) ? $query: $query->getResult();
    }

    public function findByRolesAndName(array $roles, $name, $getQuery = false)
    {
        $search = strtoupper($name);
        $dql = "
            SELECT u FROM Claroline\CoreBundle\Entity\Group u
            JOIN u.roles r WHERE r IN (:roles)
            AND UPPER(u.name) LIKE :search
            ORDER BY u.name
            ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('roles', $roles);
        $query->setParameter('search', "%{$search}%");

        return ($getQuery) ? $query: $query->getResult();
    }

    /**
     * This method should be renamed.
     * Find groups who are outside the workspace and users whose role are in $roles.
     */
    public function findOutsidersByWorkspaceRoles(array $roles, AbstractWorkspace $workspace, $getQuery = false)
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
        AbstractWorkspace $workspace,
        $getQuery = false
    )
    {
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
}
