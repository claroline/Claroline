<?php

namespace Icap\WikiBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Doctrine\ORM\QueryBuilder;

class ContributionFinder extends AbstractFinder
{
    /**
     * The queried object is already named "obj".
     */
    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null): QueryBuilder
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
                    $this->setDefaults($qb, $filterName, $filterValue);
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

        return $qb;
    }

    /** @return $string */
    public static function getClass(): string
    {
        return 'Icap\WikiBundle\Entity\Contribution';
    }
}
