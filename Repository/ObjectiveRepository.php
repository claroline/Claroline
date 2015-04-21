<?php

namespace HeVinci\CompetencyBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;

class ObjectiveRepository extends EntityRepository
{
    /**
     * Returns an array representation of all the objectives, including
     * the number of competencies associated with each objective.
     *
     * @return array
     */
    public function findWithCompetencyCount()
    {
        return $this->createQueryBuilder('o')
            ->select('o.id', 'o.name', 'COUNT(oc) AS competencyCount')
            ->leftJoin('o.objectiveCompetencies', 'oc')
            ->groupBy('o.id')
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * Returns an array representation of the objectives assigned to a user.
     *
     * @param User $user
     * @return array
     */
    public function findByUser(User $user)
    {
        return $this->createQueryBuilder('o')
            ->select('o.id', 'o.name', 'COUNT(oc) AS competencyCount')
            ->join('o.users', 'u')
            ->leftJoin('o.objectiveCompetencies', 'oc')
            ->where('u = :user')
            ->setParameter(':user', $user)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * Returns the query object for counting all the users which have
     * at least one learning objective.
     *
     * @return \Doctrine\ORM\Query
     */
    public function getUsersWithObjectiveCountQuery()
    {
        return $this->getSubjectsWithObjectiveCountQuery('users');
    }

    /**
     * Returns the query object for fetching all the users which have
     * at least one learning objective.
     *
     * @return \Doctrine\ORM\Query
     */
    public function getUsersWithObjectiveQuery()
    {
        return $this->getSubjectsWithObjectiveQuery('users', ['id', 'firstName', 'lastName']);
    }

    /**
     * Returns the query object for counting all the groups which have
     * at least one learning objective.
     *
     * @return \Doctrine\ORM\Query
     */
    public function getGroupsWithObjectiveCountQuery()
    {
        return $this->getSubjectsWithObjectiveCountQuery('groups');
    }

    /**
     * Returns the query object for fetching all the groups which have
     * at least one learning objective.
     *
     * @return \Doctrine\ORM\Query
     */
    public function getGroupsWithObjectiveQuery()
    {
        return $this->getSubjectsWithObjectiveQuery('groups', ['id', 'name']);
    }

    private function getSubjectsWithObjectiveCountQuery($subjectField)
    {
        return $this->_em->createQueryBuilder()
            ->select('COUNT(s.id)')
            ->from($this->getSubjectFqcn($subjectField), 's')
            ->where((new Expr())->in('s.id', $this->getInverseSideIdsDql($subjectField)))
            ->getQuery();
    }

    private function getSubjectsWithObjectiveQuery($subjectField, array $selectedAttributes)
    {
        $attributes = array_map(function ($attribute) {
            return "s.{$attribute}";
        }, $selectedAttributes);

        return $this->_em->createQueryBuilder()
            ->select(implode(', ', $attributes))
            ->from($this->getSubjectFqcn($subjectField), 's')
            ->where((new Expr())->in('s.id', $this->getInverseSideIdsDql($subjectField)))
            ->getQuery();
    }

    private function getSubjectFqcn($subjectField)
    {
        return $subjectField === 'users' ?
            'Claroline\CoreBundle\Entity\User' :
            'Claroline\CoreBundle\Entity\Group';
    }

    private function getInverseSideIdsDql($targetField)
    {
        return $this->createQueryBuilder('o')
            ->select('ot.id')
            ->join("o.{$targetField}", 'ot')
            ->getDQL();
    }
}
