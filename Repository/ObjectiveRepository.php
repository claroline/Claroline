<?php

namespace HeVinci\CompetencyBundle\Repository;

use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use HeVinci\CompetencyBundle\Entity\Competency;
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
            ->orderBy('o.name')
            ->groupBy('o.id')
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * Returns the objectives assigned to a user. Objectives assigned to
     * groups whose the user is a member of are also returned.
     *
     * @param User  $user
     * @param bool  $asArray
     * @return array
     */
    public function findByUser(User $user, $asArray = true)
    {
        $groupQb = $this->_em->createQueryBuilder()
            ->select('g')
            ->from('Claroline\CoreBundle\Entity\Group', 'g')
            ->join('g.users', 'gu')
            ->where('gu = :user');

        $select = $asArray ? 'o.id, o.name, op.percentage AS progress, COUNT(oc) AS competencyCount' : 'o';

        $query = $this->createQueryBuilder('o')
            ->select($select)
            ->leftJoin('o.objectiveCompetencies', 'oc')
            ->leftJoin('o.users', 'ou')
            ->leftJoin('o.groups', 'og')
            ->leftJoin(
                'HeVinci\CompetencyBundle\Entity\Progress\ObjectiveProgress',
                'op',
                'WITH',
                'op.user = :user AND op.objective = o'
            )
            ->andWhere($groupQb->expr()->orX(
                'ou = :user',
                $groupQb->expr()->in('og', $groupQb->getQuery()->getDQL())
            ))
            ->groupBy('o.id')
            ->setParameter(':user', $user)
            ->getQuery();

        return $query->{$asArray ? 'getArrayResult' : 'getResult'}();
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
            ->select('o.id', 'o.name', 'g.id AS groupId')
            ->join('o.groups', 'g')
            ->where('g = :group')
            ->groupBy('o.id')
            ->setParameter(':group', $group)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * Returns the objectives assigned to a user which includes a
     * given competency. Objectives assigned to groups whose the
     * user is a member of are also returned.
     *
     * @param Competency    $competency
     * @param User          $user
     * @return array
     */
    public function findByCompetencyAndUser(Competency $competency, User $user)
    {
        $groupQb = $this->_em->createQueryBuilder()
            ->select('g')
            ->from('Claroline\CoreBundle\Entity\Group', 'g')
            ->join('g.users', 'gu')
            ->where('gu = :user');

        return $this->createQueryBuilder('o')
            ->select('o')
            ->leftJoin('o.objectiveCompetencies', 'oc')
            ->leftJoin('o.users', 'ou')
            ->leftJoin('o.groups', 'og')
            ->where('oc.competency = :competency')
            ->andWhere($groupQb->expr()->orX(
                'ou = :user',
                $groupQb->expr()->in('og', $groupQb->getQuery()->getDQL())
            ))
            ->setParameters([
                'competency' => $competency,
                'user' => $user
            ])
            ->distinct()
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns all the users and group members who have a given objective.
     *
     * @param Objective $objective
     * @return array
     */
    public function findUsersWithObjective(Objective $objective)
    {
        $usersQb = $this->createQueryBuilder('o1')
            ->select('ou.id')
            ->join('o1.users', 'ou')
            ->where('o1 = :objective');
        $groupsQb = $this->createQueryBuilder('o2')
            ->select('og.id')
            ->join('o2.groups', 'og')
            ->where('o2 = :objective');

        return $this->_em->createQueryBuilder()
            ->select('u')
            ->from('Claroline\CoreBundle\Entity\User', 'u')
            ->leftJoin('HeVinci\CompetencyBundle\Entity\Progress\UserProgress', 'up', 'WITH', 'up.user = u')
            ->leftJoin('u.groups', 'ug')
            ->where((new Expr())->in('u.id', $usersQb->getDQL()))
            ->orWhere((new Expr())->in('ug.id', $groupsQb->getDQL()))
            ->setParameter(':objective', $objective)
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns the query object for counting all the users who have
     * -- or are member of a group which has -- at least one learning
     * objective. If a particular objective is given, only the users
     * who have that objective are counted.
     *
     * @param Objective $objective
     * @return \Doctrine\ORM\Query
     */
    public function getUsersWithObjectiveCountQuery(Objective $objective = null)
    {
        return $this->doGetUsersWithObjectiveQuery($objective, true);
    }

    /**
     * Returns the query object for fetching all the users who have
     * -- or are member of a group which has -- at least one learning
     * objective. If a particular objective is given, only the users
     * who have that objective are included.
     *
     * @param Objective $objective
     * @return \Doctrine\ORM\Query
     */
    public function getUsersWithObjectiveQuery(Objective $objective = null)
    {
        return $this->doGetUsersWithObjectiveQuery($objective, false);
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
        return $this->doGetGroupsWithObjectiveQuery($objective, true);
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
        return $this->doGetGroupsWithObjectiveQuery($objective, false);
    }

    private function doGetUsersWithObjectiveQuery(Objective $objective = null, $countOnly)
    {
        $usersQb = $this->createQueryBuilder('o1')
            ->select('ou.id')
            ->join('o1.users', 'ou');
        $groupsQb = $this->createQueryBuilder('o2')
            ->select('og.id')
            ->join('o2.groups', 'og');

        if ($objective) {
            $usersQb->where('o1 = :objective');
            $groupsQb->where('o2 = :objective');
        }

        $select = $countOnly ? 'COUNT(u.id)' : 'u.id, u.firstName, u.lastName, up.percentage AS progress';

        $qb = $this->_em->createQueryBuilder()
            ->select($select)
            ->from('Claroline\CoreBundle\Entity\User', 'u')
            ->leftJoin('HeVinci\CompetencyBundle\Entity\Progress\UserProgress', 'up', 'WITH', 'up.user = u')
            ->leftJoin('u.groups', 'ug')
            ->where((new Expr())->in('u.id', $usersQb->getDQL()))
            ->orWhere((new Expr())->in('ug.id', $groupsQb->getDQL()))
            ->orderBy('u.firstName, u.lastName', 'ASC');

        if ($objective) {
            $qb->setParameter(':objective', $objective);
        }

        return $qb->getQuery();
    }

    private function doGetGroupsWithObjectiveQuery(Objective $objective = null, $countOnly)
    {
        $groupsQb = $this->createQueryBuilder('o')
            ->select('og.id')
            ->join('o.groups', 'og');

        if ($objective) {
            $groupsQb->where('o = :objective');
        }

        $select = $countOnly ? 'COUNT(g.id)' : 'g.id, g.name';

        $qb = $this->_em->createQueryBuilder()
            ->select($select)
            ->from('Claroline\CoreBundle\Entity\Group', 'g')
            ->where((new Expr())->in('g.id', $groupsQb->getDQL()))
            ->orderBy('g.name', 'ASC');

        if ($objective) {
            $qb->setParameter(':objective', $objective);
        }

        return $qb->getQuery();
    }
}
