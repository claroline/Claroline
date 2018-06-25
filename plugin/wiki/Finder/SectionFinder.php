<?php

namespace Icap\WikiBundle\Finder;

use Claroline\AppBundle\API\FinderInterface;
use Doctrine\ORM\QueryBuilder;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.api.finder.wiki.section")
 * @DI\Tag("claroline.finder")
 */
class SectionFinder implements FinderInterface
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
                    if (is_string($filterValue)) {
                        $qb->andWhere("UPPER(obj.{$filterName}) LIKE :{$filterName}");
                        $qb->setParameter($filterName, '%'.strtoupper($filterValue).'%');
                    } else {
                        $qb->andWhere("obj.{$filterName} = :{$filterName}");
                        $qb->setParameter($filterName, $filterValue);
                    }
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
    }

    /** @return $string */
    public function getClass()
    {
        return 'Icap\WikiBundle\Entity\Section';
    }
}
