<?php

namespace HeVinci\CompetencyBundle\Repository;

use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use HeVinci\CompetencyBundle\Entity\Objective;

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
            ->groupBy('o.id')
            ->setParameter(':user', $user)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * Returns an array representation of the objectives assigned to a group.
     *
     * @param Group $group
     * @return array
     */
    public function findByGroup(Group $group)
    {
        return $this->createQueryBuilder('o')
            ->select('o.id', 'o.name')
            ->join('o.groups', 'g')
            ->where('g = :group')
            ->groupBy('o.id')
            ->setParameter(':group', $group)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * Returns the query object for counting all the users who have at
     * least one learning objective. If a particular objective is given,
     * only the users who have that objective are counted.
     *
     * @param Objective $objective
     * @return \Doctrine\ORM\Query
     */
    public function getUsersWithObjectiveCountQuery(Objective $objective = null)
    {
        return $this->getSubjectsWithObjectiveCountQuery('users', $objective);
    }

    /**
     * Returns the query object for fetching all the users who have at
     * east one learning objective. If a particular objective is given,
     * only the users who have that objective are included.
     *
     * @param Objective $objective
     * @return \Doctrine\ORM\Query
     */
    public function getUsersWithObjectiveQuery(Objective $objective = null)
    {
        return $this->getSubjectsWithObjectiveQuery('users', ['id', 'firstName', 'lastName'], $objective);
    }

    /**
     * Returns the query object for counting all the groups which have at
     * least one learning objective. If a particular objective is given,
     * only the groups which have that objective are counted.
     *
     * @param Objective $objective
     * @return \Doctrine\ORM\Query
     */
    public function getGroupsWithObjectiveCountQuery(Objective $objective = null)
    {
        return $this->getSubjectsWithObjectiveCountQuery('groups', $objective);
    }

    /**
     * Returns the query object for fetching all the groups which have at
     * least one learning objective. If a particular objective is given,
     * only the groups which have that objective are counted.
     *
     * @param Objective $objective
     * @return \Doctrine\ORM\Query
     */
    public function getGroupsWithObjectiveQuery(Objective $objective = null)
    {
        return $this->getSubjectsWithObjectiveQuery('groups', ['id', 'name'], $objective);
    }

    private function getSubjectsWithObjectiveCountQuery($subjectField, Objective $objective = null)
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('COUNT(s.id)')
            ->from($this->getSubjectFqcn($subjectField), 's')
            ->where((new Expr())->in('s.id', $this->getInverseSideIdsDql($subjectField, $objective)));

        if ($objective) {
            $qb->setParameter(':objective', $objective);
        }

        return $qb->getQuery();
    }

    private function getSubjectsWithObjectiveQuery(
        $subjectField,
        array $selectedAttributes,
        Objective $objective = null
    )
    {
        $attributes = array_map(function ($attribute) {
            return "s.{$attribute}";
        }, $selectedAttributes);

        $qb = $this->_em->createQueryBuilder()
            ->select(implode(', ', $attributes))
            ->from($this->getSubjectFqcn($subjectField), 's')
            ->where((new Expr())->in('s.id', $this->getInverseSideIdsDql($subjectField, $objective)));

        if ($objective) {
            $qb->setParameter(':objective', $objective);
        }

        return $qb->getQuery();
    }

    private function getSubjectFqcn($subjectField)
    {
        return $subjectField === 'users' ?
            'Claroline\CoreBundle\Entity\User' :
            'Claroline\CoreBundle\Entity\Group';
    }

    private function getInverseSideIdsDql($targetField, Objective $objective = null)
    {
        $qb = $this->createQueryBuilder('o')
            ->select('ot.id')
            ->join("o.{$targetField}", 'ot');

        if ($objective) {
            $qb->where('o = :objective');
        }

        return $qb->getDQL();
    }
}
