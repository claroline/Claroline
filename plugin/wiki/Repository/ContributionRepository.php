<?php

namespace Icap\WikiBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Icap\WikiBundle\Entity\Section;

class ContributionRepository extends EntityRepository
{
    /**
     * @param Section $section
     *
     * @return array $contributions
     */
    public function getSectionHistoryQuery(Section $section)
    {
        $queryBuilder = $this->createQueryBuilder('contribution')
            ->orderBy('contribution.creationDate', 'DESC')
            ->andWhere('contribution.section = :section')
            ->setParameter('section', $section);

        return $queryBuilder->getQuery();
    }

    /**
     * @param Section $section
     *
     * @return array $contributions
     */
    public function findAllButActiveForSection(Section $section)
    {
        $queryBuilder = $this->createQueryBuilder('contribution')
            ->orderBy('contribution.creationDate', 'DESC')
            ->andWhere('contribution.section = :section')
            ->andWhere('contribution.id != :activeId')
            ->setParameter('section', $section)
            ->setParameter('activeId', $section->getActiveContribution()->getId());

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param Section $section
     * @param array   $ids
     *
     * @return array $contributions
     */
    public function findyBySectionAndIds(Section $section, $ids)
    {
        $queryBuilder = $this->createQueryBuilder('contribution');
        $queryBuilder
            ->orderBy('contribution.creationDate', 'ASC')
            ->andWhere('contribution.section = :section')
            ->setParameter('section', $section)
            ->andWhere($queryBuilder->expr()->in('contribution.id', $ids));

        return $queryBuilder->getQuery()->getResult();
    }

    public function findAllForSection(Section $section)
    {
        $queryBuilder = $this->createQueryBuilder('contribution')
            ->orderBy('contribution.creationDate', 'DESC')
            ->andWhere('contribution.section = :section')
            ->setParameter('section', $section);

        return $queryBuilder->getQuery()->getResult();
    }
}
