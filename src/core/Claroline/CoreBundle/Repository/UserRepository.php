<?php

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;

class UserRepository extends EntityRepository
{
    //todo prepared statement here
    public function getUsersByUsernameList(array $usernames)
    {
        $nameList = array_map(
            function($name) {
                return "'{$name}'";
            }, $usernames
        );
        $nameList = implode(', ', $nameList);
        $dql = "
            SELECT u FROM Claroline\CoreBundle\Entity\User u
            WHERE u.username IN ({$nameList})
            ORDER BY u.username
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function getUsersOfWorkspace(AbstractWorkspace $workspace)
    {
        $dql = "
            SELECT u FROM Claroline\CoreBundle\Entity\User u
            JOIN u.workspaceRoles wr JOIN wr.workspace w WHERE w.id = :id
        ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('id', $workspace->getId());

        return $query->getResult();
    }

    //doctrine doesn't have any DQL LIMIT clause.
    public function getLazyUnregisteredUsersOfWorkspace(AbstractWorkspace $workspace, $numberIteration, $userAmount)
    {
        $offset = $numberIteration * $userAmount;
        $dql = "
            SELECT u FROM Claroline\CoreBundle\Entity\User u
            WHERE u NOT IN
            (
                SELECT us FROM Claroline\CoreBundle\Entity\User us
                JOIN us.workspaceRoles wr
                JOIN wr.workspace w
                WHERE w.id = :id
            )
        ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('id', $workspace->getId());
        $query->setMaxResults($userAmount);
        $query->setFirstResult($offset);

        return $query->getResult();
    }

    public function getUnregisteredUsersOfWorkspaceFromGenericSearch($search, AbstractWorkspace $workspace)
    {
        $search = strtoupper($search);

        $dql = "
            SELECT u FROM Claroline\CoreBundle\Entity\User u
            WHERE UPPER(u.lastName) LIKE :search
            AND u NOT IN
            (
                SELECT us FROM Claroline\CoreBundle\Entity\User us
                JOIN us.workspaceRoles wr
                JOIN wr.workspace w
                WHERE w.id = :id
            )
            OR UPPER(u.firstName) LIKE :search
            AND u NOT IN
            (
                SELECT use FROM Claroline\CoreBundle\Entity\User use
                JOIN use.workspaceRoles wro
                JOIN wro.workspace wo
                WHERE wo.id = :id
            )
            OR UPPER(u.username) LIKE :search
            AND u NOT IN
            (
                SELECT user FROM Claroline\CoreBundle\Entity\User user
                JOIN user.workspaceRoles wrol
                JOIN wrol.workspace wol
                WHERE wol.id = :id
            )
        ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('id', $workspace->getId());
        $query->setParameter('search',"%{$search}%");
        $query->setMaxResults(200);

        return $query->getResult();
    }


    public function findPaginatedUsers($page, $limit)
    {
        $offset = $limit * (--$page);
        $qb = $this->_em->createQueryBuilder();
        $qb->add('select', 'u')
            ->add('from', 'Claroline\CoreBundle\Entity\User u')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        $q = $qb->getQuery();

        return $q->getResult();
    }

    public function findPaginatedUsersOfGroup($groupId, $page, $limit)
    {
      $offset = $limit * (--$page);

        $dql = "
            SELECT u from Claroline\CoreBundle\Entity\User u
            JOIN u.groups g WHERE g.id = :groupId";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('groupId', $groupId);
        $query->setFirstResult($offset);
        $query->setMaxResults($limit);

        return $query->getResult();
    }

    public function findPaginatedUsersOfWorkspace($workspaceId, $offset, $limit)
    {
        $dql = "
            SELECT u from Claroline\CoreBundle\Entity\User u
            JOIN u.workspaceRoles wr JOIN wr.workspace w WHERE w.id = :workspaceId";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspaceId', $workspaceId);
        $query->setFirstResult($offset);
        $query->setMaxResults($limit);

        return $query->getResult();
    }
}