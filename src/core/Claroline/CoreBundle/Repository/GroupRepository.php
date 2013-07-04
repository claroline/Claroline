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


    

    public function findByWorkspace(AbstractWorkspace $workspace, $getQuery = false)
    {
        $dql = "
            SELECT g, wr
            FROM Claroline\CoreBundle\Entity\Group g
            LEFT JOIN g.roles wr WITH wr IN (
                SELECT pr from Claroline\CoreBundle\Entity\Role pr WHERE pr.type = ".Role::WS_ROLE."
            )
            LEFT JOIN wr.workspace w
            WHERE w.id = :workspaceId
       ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspaceId', $workspace->getId());

        return ($getQuery) ? $query: $query->getResult();
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
     * Returns the groups of the platform
     */
    public function findAll($getQuery = false)
    {
        if ($getQuery) {
            $dql = "
                SELECT g, r FROM Claroline\CoreBundle\Entity\Group g
                  LEFT JOIN g.roles r";

             return $this->_em->createQuery($dql);
        }

        return parent::findAll();
    }

    /**
     * Search the groups of the platform
     *
     * @param string  $search
     * @param boolean $getQuery
     *
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     */
    public function findByName($search, $getQuery = false)
    {
        $search = strtoupper($search);

        $dql = "
            SELECT g, r
            FROM Claroline\CoreBundle\Entity\Group g
            LEFT JOIN g.roles r
            WHERE UPPER(g.name) LIKE :search
        ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('search', "%{$search}%");

        return ($getQuery) ? $query: $query->getResult();
    }
}