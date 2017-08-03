<?php

namespace HeVinci\CompetencyBundle\Repository;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use HeVinci\CompetencyBundle\Entity\Ability;
use HeVinci\CompetencyBundle\Entity\Competency;
use HeVinci\CompetencyBundle\Entity\Level;

class AbilityRepository extends EntityRepository
{
    /**
     * Returns an array representation of all the abilities linked
     * to a given competency tree. Result includes information
     * about ability level as well.
     *
     * @param Competency $competency
     *
     * @return array
     */
    public function findByCompetency(Competency $competency)
    {
        return $this->createQueryBuilder('a')
            ->select(
                'a.id',
                'a.name',
                'a.resourceCount',
                'a.minResourceCount',
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
                ':rgt' => $competency->getRight(),
            ])
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * Returns the abilities directly linked to a given competency and
     * a particular level, excluding a given ability.
     *
     * @param Competency $competency
     * @param Level      $level
     *
     * @return array
     */
    public function findOthersByCompetencyAndLevel(
        Competency $competency,
        Level $level,
        Ability $abilityToExclude
    ) {
        return $this->createQueryBuilder('a')
            ->select('a')
            ->join('a.competencyAbilities', 'ca')
            ->join('ca.competency', 'c')
            ->join('ca.level', 'l')
            ->where('c = :competency')
            ->andWhere('l = :level')
            ->andWhere('a <> :excluded')
            ->orderBy('l.value, a.id')
            ->setParameters([
                ':competency' => $competency,
                ':level' => $level,
                ':excluded' => $abilityToExclude,
            ])
            ->getQuery()
            ->getResult();
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
     * @param string     $name
     * @param Competency $excludedParent
     *
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
            ->setParameter(':name', $name.'%')
            ->setParameter(':parent', $excludedParent)
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns the abilities associated with a resource, pre-loading
     * level information.
     *
     * @param ResourceNode $resource
     *
     * @return array
     */
    public function findByResource(ResourceNode $resource)
    {
        return $this->createQueryBuilder('a')
            ->select('a', 'ca', 'c', 'l')
            ->join('a.resources', 'r')
            ->join('a.competencyAbilities', 'ca')
            ->join('ca.competency', 'c')
            ->join('ca.level', 'l')
            ->where('r = :resource')
            ->setParameter(':resource', $resource)
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns an array representation of resource evaluation data for
     * a given user and a given competency, including information about
     * the resource and the related abilities.
     *
     * @param Competency $competency
     * @param User       $user
     *
     * @return array
     *
     * @throws \Exception
     */
    public function findEvaluationsByCompetency(Competency $competency, User $user)
    {
        if ($competency->getRight() - $competency->getLeft() > 1) {
            throw new \Exception('Expected leaf competency');
        }

        $resourceQb = $this->createQueryBuilder('a1')
            ->select('node.id')
            ->join('a1.resources', 'node')
            ->join('a1.competencyAbilities', 'ca')
            ->where('ca.competency = :competency');

        return $this->_em->createQueryBuilder()
            ->select(
                'e.id AS evaluationId',
                'e.status',
                'e.date',
                'n.id AS resourceId',
                'n.name AS resourceName',
                'a.id AS abilityId',
                'a.name AS abilityName',
                'l.name AS levelName'
            )
            ->from('Claroline\CoreBundle\Entity\Resource\ResourceEvaluation', 'e')
            ->join('e.resourceUserEvaluation', 'eru')
            ->join('eru.user', 'u')
            ->join('eru.resourceNode', 'n')
            ->join(
                'HeVinci\CompetencyBundle\Entity\Ability',
                'a',
                'WITH',
                'n IN (SELECT node2 FROM HeVinci\CompetencyBundle\Entity\Ability a2 JOIN a2.resources node2 WHERE a2 = a)'
            )
            ->join('a.competencyAbilities', 'ca2')
            ->join('ca2.level', 'l')
            ->join('ca2.competency', 'c2')
            ->where('c2 = :competency')
            ->where($resourceQb->expr()->in(
                'n',
                $resourceQb->getQuery()->getDQL()
            ))
            ->andWhere('u = :user')
            ->orderBy('e.date, e.id, a.id', 'ASC')
            ->setParameters([
                ':competency' => $competency,
                ':user' => $user,
            ])
            ->getQuery()
            ->getArrayResult();
    }
}
