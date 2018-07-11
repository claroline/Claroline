<?php

namespace Icap\WikiBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Doctrine\ORM\QueryBuilder;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.api.finder.wiki.section.contribution")
 * @DI\Tag("claroline.finder")
 */
class ContributionFinder extends AbstractFinder
{
    /**
     * The queried object is already named "obj".
     *
     * @param QueryBuilder $qb
     * @param array        $searches
     * @param array|null   $sortBy
     */
    public function configureQueryBuilder(QueryBuilder $qb, array $searches, array $sortBy = null)
    {
        $joinedCreator = false;
        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'creator':
                    $joinedCreator = true;
                    $qb
                        ->join('obj.contributor', 'contributor')
                        ->andWhere($qb->expr()->orX(
                            $qb->expr()->like('UPPER(contributor.firstName)', ':contributor'),
                            $qb->expr()->like('UPPER(contributor.lastName)', ':contributor'),
                            $qb->expr()->like('UPPER(contributor.username)', ':contributor'),
                            $qb->expr()->like('UPPER(contributor.email)', ':contributor'),
                            $qb->expr()->like(
                                "CONCAT(CONCAT(UPPER(contributor.firstName), ' '), UPPER(contributor.lastName))",
                                ':contributor'
                            ),
                            $qb->expr()->like(
                                "CONCAT(CONCAT(UPPER(contributor.lastName), ' '), UPPER(contributor.firstName))",
                                ':contributor'
                            )
                        ))
                        ->setParameter('contributor', '%'.strtoupper($filterValue).'%');
                    break;

                default:
                    if (is_string($filterValue)) {
                        $qb->andWhere("UPPER(obj.{$filterName}) LIKE :{$filterName}");
                        $qb->setParameter($filterName, '%'.strtoupper($filterValue).'%');
                    } else {
                        $qb->andWhere("obj.{$filterName} = :{$filterName}");
                        $qb->setParameter($filterName, $filterValue);
                    }
            }
        }

        if (null !== $sortBy && 'creator' === $sortBy['property']) {
            $direction = 1 === $sortBy['direction'] ? 'ASC' : 'DESC';
            if (!$joinedCreator) {
                $qb->join('obj.contributor', 'contributor');
            }
            $qb
                ->addOrderBy('contributor.lastName', $direction)
                ->addOrderBy('contributor.firstName', $direction);
        }
    }

    /** @return $string */
    public function getClass()
    {
        return 'Icap\WikiBundle\Entity\Contribution';
    }
}
