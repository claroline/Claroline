<?php

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;

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

    public function getLazyUnregisteredGroupsOfWorkspace(AbstractWorkspace $workspace, $offset, $limit)
    {
        $dql = "
            SELECT g FROM Claroline\CoreBundle\Entity\Group g
            WHERE g NOT IN
            (
                SELECT gr FROM Claroline\CoreBundle\Entity\Group gr
                JOIN gr.workspaceRoles wr
                JOIN wr.workspace w
                WHERE w.id = :id
            )
       ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('id', $workspace->getId());
        $query->setMaxResults($limit);
        $query->setFirstResult($offset);

        return $query->getResult();
    }

    public function searchPaginatedUnregisteredGroupsOfWorkspace($search, AbstractWorkspace $workspace, $offset, $limit)
    {
        $search = strtoupper($search);

        $dql = "
            SELECT g FROM Claroline\CoreBundle\Entity\Group g
            WHERE UPPER(g.name) LIKE '%" . $search . "%'
            AND g NOT IN
            (
                SELECT gr FROM Claroline\CoreBundle\Entity\Group gr
                JOIN gr.workspaceRoles wr
                JOIN wr.workspace w
                WHERE w.id = :id
            )
        ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('id', $workspace->getId())
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $query->getResult();
    }

    public function searchPaginatedRegisteredGroupsOfWorkspace($search, AbstractWorkspace $workspace, $offset, $limit)
    {
        $search = strtoupper($search);

        $dql = "
            SELECT g FROM Claroline\CoreBundle\Entity\Group g
            WHERE UPPER(g.name) LIKE '%" . $search . "%'
            AND g IN
            (
                SELECT gr FROM Claroline\CoreBundle\Entity\Group gr
                JOIN gr.workspaceRoles wr
                JOIN wr.workspace w
                WHERE w.id = :id
            )
        ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('id', $workspace->getId())
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $query->getResult();
    }

    public function findPaginatedGroups($offset, $limit)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->add('select', 'g')
            ->add('from', 'Claroline\CoreBundle\Entity\Group g')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        $q = $qb->getQuery();

        return $q->getResult();
    }

    public function findPaginatedGroupsOfWorkspace($workspaceId, $offset, $limit)
    {
        $dql = "
            SELECT g FROM Claroline\CoreBundle\Entity\Group g
            JOIN g.workspaceRoles wr JOIN wr.workspace w WHERE w.id = :workspaceId
       ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspaceId', $workspaceId);
        $query->setFirstResult($offset);
        $query->setMaxResults($limit);

        return $query->getResult();
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