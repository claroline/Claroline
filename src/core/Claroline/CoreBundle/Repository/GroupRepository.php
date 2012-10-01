<?php

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Doctrine\ORM\Tools\Pagination\Paginator;


class GroupRepository extends EntityRepository
{
    public function getGroupsOfWorkspace(AbstractWorkspace $workspace, $role = null)
    {
        $dql = "
            SELECT g FROM Claroline\CoreBundle\Entity\Group g
            JOIN g.workspaceRoles wr JOIN wr.workspace w WHERE w.id = :id
       ";

        if ($role != null) {
            $dql.= "AND wr.id = :roleId";
        }

        $query = $this->_em->createQuery($dql);
        $query->setParameter('id', $workspace->getId());

        if ($role != null){
            $query->setParameter('roleId', $role->getId());
        }

        return $query->getResult();
    }

    public function unregisteredGroupsOfWorkspace(AbstractWorkspace $workspace, $offset, $limit)
    {
        $dql = "
            SELECT g, r, gwr FROM Claroline\CoreBundle\Entity\Group g
            LEFT JOIN g.roles r
            LEFT JOIN g.workspaceRoles gwr

            WHERE g NOT IN
            (
                SELECT gr FROM Claroline\CoreBundle\Entity\Group gr
                JOIN gr.workspaceRoles wr
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

    public function searchUnregisteredGroupsOfWorkspace($search, AbstractWorkspace $workspace, $offset, $limit)
    {
        $search = strtoupper($search);

        $dql = "
            SELECT g, r, gwr FROM Claroline\CoreBundle\Entity\Group g
            LEFT JOIN g.roles r
            LEFT JOIN g.workspaceRoles gwr

            WHERE UPPER(g.name) LIKE :search
            AND g NOT IN
            (
                SELECT gr FROM Claroline\CoreBundle\Entity\Group gr
                JOIN gr.workspaceRoles wr
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

    public function searchRegisteredGroupsOfWorkspace($search, AbstractWorkspace $workspace, $offset, $limit)
    {
        $search = strtoupper($search);

        $dql = "
            SELECT g, r, gwr FROM Claroline\CoreBundle\Entity\Group g
            LEFT JOIN g.roles r
            LEFT JOIN g.workspaceRoles gwr
            WHERE UPPER(g.name) LIKE :search
            AND g IN
            (
                SELECT gr FROM Claroline\CoreBundle\Entity\Group gr
                JOIN gr.workspaceRoles wr
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

    public function groups($offset, $limit)
    {
        $dql = "
            SELECT g, r, gwr FROM Claroline\CoreBundle\Entity\Group g
              LEFT JOIN g.roles r
              LEFT JOIN g.workspaceRoles gwr";

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
            SELECT g, r, gwr
            FROM Claroline\CoreBundle\Entity\Group g
              LEFT JOIN g.roles r
              LEFT JOIN g.workspaceRoles gwr
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
            SELECT g, wr, r
            FROM Claroline\CoreBundle\Entity\Group g
            LEFT JOIN g.roles r
            JOIN g.workspaceRoles wr JOIN wr.workspace w WHERE w.id = :workspaceId
       ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspaceId', $workspaceId);
        $query->setFirstResult($offset);
        $query->setMaxResults($limit);

        $paginator = new Paginator($query, true);

        return $paginator;
    }

    public function getRoleOfWorkspace($groupId, $workspaceId)
    {
        $dql = "
            SELECT wr FROM Claroline\CoreBundle\Entity\WorkspaceRole wr
            JOIN wr.workspace ws
            JOIN wr.groups g
            WHERE ws.id = :workspaceId
            AND g.id = :groupId
       ";

       $query = $this->_em->createQuery($dql);
       $query->setParameter('workspaceId', $workspaceId);
       $query->setParameter('groupId', $groupId);

       return $query->getResult();
    }
}