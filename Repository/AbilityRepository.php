<?php

namespace HeVinci\CompetencyBundle\Repository;

use Doctrine\ORM\EntityRepository;
use HeVinci\CompetencyBundle\Entity\Competency;

class AbilityRepository extends EntityRepository
{
    /**
     * Returns an array representation of all the abilities linked
     * to a given competency framework. Result includes information
     * about ability level as well.
     *
     * @param Competency $framework
     * @return array
     */
    public function findByFramework(Competency $framework)
    {
        return $this->createQueryBuilder('a')
            ->select(
                'a.id',
                'a.name',
                'a.minActivityCount',
                'c.id AS competencyId',
                'l.name AS levelName',
                'l.value AS levelValue'
            )
            ->join('a.competencyAbilities', 'ca')
            ->join('ca.competency', 'c')
            ->join('ca.level', 'l')
            ->where('c.root = :root')
            ->setParameter(':root', $framework->getId())
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

        $linkedAbilityIds = array_map(function ($element) {
            return $element['id'];
        }, $linkedAbilityIds);

        $qb = $this->createQueryBuilder('a');
        $qb->delete()
            ->where($qb->expr()->notIn('a.id', $linkedAbilityIds))
            ->getQuery()
            ->execute();
    }
}
