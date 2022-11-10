<?php

namespace Icap\WikiBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Doctrine\ORM\QueryBuilder;

class SectionFinder extends AbstractFinder
{
    /**
     * The queried object is already named "obj".
     */
    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null): QueryBuilder
    {
        $qb->join('obj.activeContribution', 'contribution');

        $joinedCreator = false;
        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'contribution':
                    $qb
                        ->andWhere("UPPER(contribution.title) LIKE :{$filterName}")
                        ->setParameter($filterName, '%'.strtoupper($filterValue).'%');
                    break;
                case 'creator':
                    $joinedCreator = true;
                    $qb
                        ->join('obj.author', 'author')
                        ->andWhere($qb->expr()->orX(
                            $qb->expr()->like('UPPER(author.firstName)', ':author'),
                            $qb->expr()->like('UPPER(author.lastName)', ':author'),
                            $qb->expr()->like('UPPER(author.username)', ':author'),
                            $qb->expr()->like('UPPER(author.email)', ':author'),
                            $qb->expr()->like(
                                "CONCAT(CONCAT(UPPER(author.firstName), ' '), UPPER(author.lastName))",
                                ':author'
                            ),
                            $qb->expr()->like(
                                "CONCAT(CONCAT(UPPER(author.lastName), ' '), UPPER(author.firstName))",
                                ':author'
                            )
                        ))
                        ->setParameter('author', '%'.strtoupper($filterValue).'%');
                    break;

                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
                    break;
            }
        }

        if (null !== $sortBy && ('creator' === $sortBy['property'] || 'contribution' === $sortBy['property'])) {
            $direction = 1 === $sortBy['direction'] ? 'ASC' : 'DESC';
            switch ($sortBy['property']) {
                case 'creator':
                    if (!$joinedCreator) {
                        $qb->join('obj.author', 'author');
                    }
                    $qb
                        ->addOrderBy('author.lastName', $direction)
                        ->addOrderBy('author.firstName', $direction);
                    break;

                case 'contribution':
                    $qb->addOrderBy('contribution.title', $direction);
            }
        }

        return $qb;
    }

    /** @return $string */
    public static function getClass(): string
    {
        return 'Icap\WikiBundle\Entity\Section';
    }
}
