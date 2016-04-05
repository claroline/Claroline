<?php

namespace HeVinci\CompetencyBundle\Repository;

use Doctrine\ORM\EntityRepository;
use HeVinci\CompetencyBundle\Entity\Ability;
use HeVinci\CompetencyBundle\Entity\Competency;

class CompetencyAbilityRepository extends EntityRepository
{
    /**
     * Returns the number of existing associations between
     * competencies and a given ability.
     *
     * @param Ability $ability
     * @return integer
     */
    public function countByAbility(Ability $ability)
    {
        return $this->createQueryBuilder('ca')
            ->select('COUNT(ca)')
            ->where('ca.ability = :ability')
            ->setParameter(':ability', $ability)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Returns the number of abilities associated with a given competency.
     *
     * @param Competency $competency
     * @return mixed
     */
    public function countByCompetency(Competency $competency)
    {
        return $this->createQueryBuilder('ca')
            ->select('COUNT(ca)')
            ->where('ca.competency = :competency')
            ->setParameter(':competency', $competency)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Returns the association between a competency and an ability.
     *
     * @param Competency $parent
     * @param Ability $ability
     * @return null|object
     * @throws \Exception if the ability is not linked to the competency
     */
    public function findOneByTerms(Competency $parent, Ability $ability)
    {
        $link = $this->findOneBy(['competency' => $parent, 'ability' => $ability]);

        if (!$link) {
            throw new \RuntimeException(
                "Competency {$parent->getId()} is not linked to ability {$ability->getId()}"
            );
        }

        return $link;
    }
}
