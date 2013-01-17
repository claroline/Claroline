<?php

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Group;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Claroline\CoreBundle\Entity\Role;


class GroupRepository extends EntityRepository
{
    public function getGroupsOfWorkspace(AbstractWorkspace $workspace, $role = null)
    {
        $dql = "
            SELECT g FROM Claroline\CoreBundle\Entity\Group g
            LEFT JOIN g.roles rg
            LEFT JOIN rg.workspaceRights rights
            JOIN rights.workspace w
            WHERE w.id = :id
       ";

        if ($role != null) {
            $dql.= "AND wr.id = :roleId";
        }

        $query = $this->_em->createQuery($dql);
        $query->setParameter('id', $workspace->getId());

        if ($role != null) {
            $query->setParameter('roleId', $role->getId());
        }

        return $query->getResult();
    }

    public function unregisteredGroupsOfWorkspace(AbstractWorkspace $workspace, $offset, $limit)
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
                JOIN wr.workspaceRights rights
                JOIN rights.workspace w
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

    public function searchUnregisteredGroupsOfWorkspace($search, AbstractWorkspace $workspace, $offset, $limit)
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
                JOIN wr.workspaceRights rights
                JOIN rights.workspace w
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

    public function searchRegisteredGroupsOfWorkspace($search, AbstractWorkspace $workspace, $offset, $limit)
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
                JOIN wr.workspaceRights rights
                JOIN rights.workspace w
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

    public function groups($offset, $limit)
    {
        $dql = "
            SELECT g, r FROM Claroline\CoreBundle\Entity\Group g
              LEFT JOIN g.roles r";

         $query = $this->_em->createQuery($dql)
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        $paginator = new Paginator($query, true);

        return $paginator;
    }

    public function searchGroups($search, $offset, $limit)
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

    public function registeredGroupsOfWorkspace($workspaceId, $offset, $limit)
    {
        $dql = "
            SELECT g, wr
            FROM Claroline\CoreBundle\Entity\Group g
            LEFT JOIN g.roles wr WITH wr IN (
                SELECT pr from Claroline\CoreBundle\Entity\Role pr WHERE pr.roleType = ".Role::WS_ROLE."
            )
            LEFT JOIN wr.workspaceRights rights
            LEFT JOIN rights.workspace w
            WHERE w.id = :workspaceId
       ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspaceId', $workspaceId);
        $query->setFirstResult($offset);
        $query->setMaxResults($limit);

        $paginator = new Paginator($query, true);

        return $paginator;
    }

    public function isRegisteredInWorkspace(AbstractWorkspace $workspace, Group $group)
    {
        $dql = "
            SELECT r FROM Claroline\CoreBundle\Entity\Role r
            JOIN r.workspaceRights wsr
            JOIN wsr.workspace ws
            JOIN r.groups g
            WHERE g.id = {$group->getId()}
            AND ws.id = {$workspace->getId()}
            ";

        $query = $this->_em->createQuery($dql);
        $result = $query->getOneOrNullResult();

        return ($result === null) ? true: false;
    }
}