<?php

namespace HeVinci\CompetencyBundle\Repository;

use Claroline\CoreBundle\Entity\Resource\Activity;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use HeVinci\CompetencyBundle\Entity\Competency;

class AbilityRepository extends EntityRepository
{
    /**
     * Returns an array representation of all the abilities linked
     * to a given competency tree. Result includes information
     * about ability level as well.
     *
     * @param Competency $competency
     * @return array
     */
    public function findByCompetency(Competency $competency)
    {
        return $this->createQueryBuilder('a')
            ->select(
                'a.id',
                'a.name',
                'a.activityCount',
                'a.minActivityCount',
                'c.id AS competencyId',
                'l.name AS levelName',
                'l.value AS levelValue'
            )
            ->join('a.competencyAbilities', 'ca')
            ->join('ca.competency', 'c')
            ->join('ca.level', 'l')
            ->where('c.root = :root')
            ->andWhere('c.lft >= :lft')
            ->andWhere('c.rgt <= :rgt')
            ->orderBy('l.value, a.id')
            ->setParameters([
                ':root' => $competency->getRoot(),
                ':lft' => $competency->getLeft(),
                ':rgt' => $competency->getRight()
            ])
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * Deletes abilities which are no longer associated with a competency.
     */
    public function deleteOrphans()
    {
        $linkedAbilityIds = $this->_em->createQueryBuilder()
            ->select('a.id')
            ->distinct()
            ->from('HeVinci\CompetencyBundle\Entity\CompetencyAbility', 'ca')
            ->join('ca.ability', 'a')
            ->getQuery()
            ->getScalarResult();

        $qb = $this->createQueryBuilder('a')->delete();

        if (count($linkedAbilityIds) > 0) {
            $linkedAbilityIds = array_map(function ($element) {
                return $element['id'];
            }, $linkedAbilityIds);
            $qb->where($qb->expr()->notIn('a.id', $linkedAbilityIds));
        }

        $qb->getQuery()->execute();
    }

    /**
     * Returns the first five abilities whose name begins by a given
     * string, excluding abilities linked to a particular competency.
     *
     * @param string        $name
     * @param Competency    $excludedParent
     * @return array
     */
    public function findFirstByName($name, Competency $excludedParent)
    {
        $qb = $this->createQueryBuilder('a');
        $qb2 = $this->_em->createQueryBuilder();
        $qb2->select('a2')
            ->from('HeVinci\CompetencyBundle\Entity\CompetencyAbility', 'ca')
            ->join('ca.ability', 'a2')
            ->where($qb2->expr()->eq('ca.competency', ':parent'));

        return $qb->select('a')
            ->where($qb->expr()->like('a.name', ':name'))
            ->andWhere($qb->expr()->notIn('a', $qb2->getDQL()))
            ->orderBy('a.name')
            ->setMaxResults(5)
            ->setParameter(':name', $name . '%')
            ->setParameter(':parent', $excludedParent)
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns the abilities associated with an activity, pre-loading
     * level information.
     *
     * @param Activity $activity
     * @return array
     */
    public function findByActivity(Activity $activity)
    {
        return $this->createQueryBuilder('a')
            ->select('a', 'ca', 'c', 'l')
            ->join('a.activities', 'ac')
            ->join('a.competencyAbilities', 'ca')
            ->join('ca.competency', 'c')
            ->join('ca.level', 'l')
            ->where('ac = :activity')
            ->setParameter(':activity', $activity)
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns an array representation of activity evaluation data for
     * a given user and a given competency, including information about
     * the activity and the related abilities.
     *
     * @param Competency    $competency
     * @param User          $user
     * @return array
     * @throws \Exception
     */
    public function findEvaluationsByCompetency(Competency $competency, User $user)
    {
        if ($competency->getRight() - $competency->getLeft() > 1) {
            throw new \Exception('Expected leaf competency');
        }

        $activityQb = $this->createQueryBuilder('a1')
            ->select('ac1.id')
            ->join('a1.activities', 'ac1')
            ->join('a1.competencyAbilities', 'ca')
            ->where('ca.competency = :competency');

        return $this->_em->createQueryBuilder()
            ->select(
                'e.id AS evaluationId',
                'e.status',
                'e.date',
                'ac.id AS activityId',
                'n.name AS activityName',
                'a.id AS abilityId',
                'a.name AS abilityName',
                'l.name AS levelName'
            )
            ->from('Claroline\CoreBundle\Entity\Activity\Evaluation', 'e')
            ->join('e.user', 'u')
            ->join('e.activityParameters', 'ap')
            ->join('ap.activity', 'ac')
            ->join('ac.resourceNode', 'n')
            ->join(
                'HeVinci\CompetencyBundle\Entity\Ability',
                'a',
                'WITH',
                'ac IN (SELECT ac2 FROM HeVinci\CompetencyBundle\Entity\Ability a2 JOIN a2.activities ac2 WHERE a2 = a)'
            )
            ->join('a.competencyAbilities', 'ca2')
            ->join('ca2.level', 'l')
            ->join('ca2.competency', 'c2')
            ->where('c2 = :competency')
            ->where($activityQb->expr()->in(
                'ac',
                $activityQb->getQuery()->getDQL()
            ))
            ->andWhere('u = :user')
            ->orderBy('e.date', 'ASC')
            ->setParameters([
                ':competency' => $competency,
                ':user' => $user
            ])
            ->getQuery()
            ->getArrayResult();
    }
}
