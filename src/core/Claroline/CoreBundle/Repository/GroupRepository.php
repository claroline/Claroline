<?php

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Claroline\CoreBundle\Entity\Role;

class GroupRepository extends EntityRepository
{
    public function findWorkspaceOutsider(AbstractWorkspace $workspace, $offset, $limit)
    {
        $dql = "
            SELECT g, r FROM Claroline\CoreBundle\Entity\Group g
            LEFT JOIN g.roles r

            WHERE g NOT IN
            (
                SELECT gr FROM Claroline\CoreBundle\Entity\Group gr
                LEFT JOIN gr.roles wr WITH wr IN (
                    SELECT pr from Claroline\CoreBundle\Entity\Role pr WHERE pr.roleType = ".Role::WS_ROLE."
                )
                JOIN wr.workspace w
                WHERE w.id = :id
            )

            ORDER BY g.id
       ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('id', $workspace->getId());
        $query->setMaxResults($limit);
        $query->setFirstResult($offset);
        $paginator = new Paginator($query, true);

        return $paginator;
    }

    public function findWorkspaceOutsiderByName(AbstractWorkspace $workspace, $search, $offset, $limit)
    {
        $search = strtoupper($search);

        $dql = "
            SELECT g, r FROM Claroline\CoreBundle\Entity\Group g
            LEFT JOIN g.roles r

            WHERE UPPER(g.name) LIKE :search
            AND g NOT IN
            (
                SELECT gr FROM Claroline\CoreBundle\Entity\Group gr
                JOIN gr.roles wr WITH wr IN (
                    SELECT pr from Claroline\CoreBundle\Entity\Role pr WHERE pr.roleType = ".Role::WS_ROLE."
                )
                JOIN wr.workspace w
                WHERE w.id = :id
            )

            ORDER BY g.id
        ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('id', $workspace->getId())
            ->setParameter('search', "%{$search}%")
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        $paginator = new Paginator($query, true);

        return $paginator;
    }

    public function findByWorkspaceAndName(AbstractWorkspace $workspace, $search, $offset, $limit)
    {
        $search = strtoupper($search);

        $dql = "
            SELECT g, r FROM Claroline\CoreBundle\Entity\Group g
            LEFT JOIN g.roles r
            WHERE UPPER(g.name) LIKE :search
            AND g IN
            (
                SELECT gr FROM Claroline\CoreBundle\Entity\Group gr
                JOIN gr.roles wr WITH wr IN (
                    SELECT pr from Claroline\CoreBundle\Entity\Role pr WHERE pr.roleType = ".Role::WS_ROLE."
                )
                JOIN wr.workspace w
                WHERE w.id = :id
            )

        ORDER BY g.id
        ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('id', $workspace->getId())
            ->setParameter('search', "%{$search}%")
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        $paginator = new Paginator($query, true);

        return $paginator;
    }

    /**
     * Returns the groups of the platform according to the limit and the offset
     *
     * @param integer $offset
     * @param integer $limit
     *
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     */
    public function findAll($offset = null, $limit = null)
    {
        if ($offset !== null && $limit !== null) {
            $dql = "
                SELECT g, r FROM Claroline\CoreBundle\Entity\Group g
                  LEFT JOIN g.roles r";

             $query = $this->_em->createQuery($dql)
                ->setFirstResult($offset)
                ->setMaxResults($limit);

            $paginator = new Paginator($query, true);

            return $paginator;
        }

        return $this->findAll();
    }

    /**
     * Search the groups of the platform according to the limit and the offset
     *
     * @param string  $search
     * @param integer $offset
     * @param integer $limit
     *
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     */
    public function findByName($search, $offset, $limit)
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
        $query->setFirstResult($offset);
        $query->setMaxResults($limit);

        $paginator = new Paginator($query, true);

        return $paginator;
    }

    public function findByWorkspace(AbstractWorkspace $workspace, $offset, $limit)
    {
        $dql = "
            SELECT g, wr
            FROM Claroline\CoreBundle\Entity\Group g
            LEFT JOIN g.roles wr WITH wr IN (
                SELECT pr from Claroline\CoreBundle\Entity\Role pr WHERE pr.roleType = ".Role::WS_ROLE."
            )
            LEFT JOIN wr.workspace w
            WHERE w.id = :workspaceId
       ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspaceId', $workspace->getId());
        $query->setFirstResult($offset);
        $query->setMaxResults($limit);

        $paginator = new Paginator($query, true);

        return $paginator;
    }
}