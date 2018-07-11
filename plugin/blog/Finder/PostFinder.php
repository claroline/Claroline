<?php

namespace Icap\BlogBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Doctrine\ORM\QueryBuilder;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.api.finder.blog.post")
 * @DI\Tag("claroline.finder")
 */
class PostFinder extends AbstractFinder
{
    public function getClass()
    {
        return 'Icap\BlogBundle\Entity\Post';
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null)
    {
        foreach ($searches as $filterName => $filterValue) {
            if ('published' === $filterName) {
                if ($filterValue) {
                    $qb
                        ->andWhere('obj.status = :status')
                        ->andWhere('obj.publicationDate <= :endOfDay')
                        ->setParameter('status', true)
                        ->setParameter('endOfDay', new \DateTime('tomorrow'));
                } else {
                    $qb
                        ->andWhere('obj.status = :status')
                        ->setParameter('status', false);
                }
            } elseif ('authorName' === $filterName) {
                $qb
                ->innerJoin('obj.author', 'author')
                ->andWhere("UPPER(author.firstName) LIKE :{$filterName} 
                            OR UPPER(author.lastName) LIKE :{$filterName} 
                            OR UPPER(CONCAT(CONCAT(author.firstName, ' '), author.lastName)) LIKE :{$filterName}
                            OR UPPER(CONCAT(CONCAT(author.lastName, ' '), author.firstName)) LIKE :{$filterName}
                            ");
                $qb->setParameter($filterName, '%'.strtoupper($filterValue).'%');
            } elseif ('publicationDate' === $filterName) {
                $date = DateNormalizer::denormalize($filterValue);

                $beginOfDay = clone $date;
                $beginOfDay->modify('today');
                $endOfDay = clone $beginOfDay;
                $endOfDay->modify('tomorrow');
                $endOfDay->modify('1 second ago');

                $qb
                    ->andWhere("obj.{$filterName} >= :beginOfDay")
                    ->andWhere("obj.{$filterName} <= :endOfDay")
                    ->setParameter(':beginOfDay', $beginOfDay)
                    ->setParameter(':endOfDay', $endOfDay);
            } elseif ('fromDate' === $filterName) {
                $date = DateNormalizer::denormalize($filterValue);
                $beginOfDay = clone $date;
                $beginOfDay->modify('today');

                $qb
                ->andWhere('obj.publicationDate >= :beginOfDay')
                ->setParameter(':beginOfDay', $beginOfDay);
            } elseif ('toDate' === $filterName) {
                $date = DateNormalizer::denormalize($filterValue);
                $beginOfDay = clone $date;
                $beginOfDay->modify('today');
                $endOfDay = clone $beginOfDay;
                $endOfDay->modify('tomorrow');
                $endOfDay->modify('1 second ago');

                $qb
                    ->andWhere('obj.publicationDate <= :endOfDay')
                    ->setParameter(':endOfDay', $endOfDay);
            } elseif ('tags' === $filterName) {
                $qb->andWhere("obj.uuid IN (
                  SELECT to.objectId
                  FROM Claroline\TagBundle\Entity\TaggedObject to
                  INNER JOIN to.tag t
                  WHERE UPPER(t.name) LIKE :tagFilter
                )");
                $qb->setParameter('tagFilter', '%'.strtoupper($filterValue).'%');
            } elseif (is_string($filterValue)) {
                $qb->andWhere("UPPER(obj.{$filterName}) LIKE :{$filterName}");
                $qb->setParameter($filterName, '%'.strtoupper($filterValue).'%');
            } else {
                $qb->andWhere("obj.{$filterName} = :{$filterName}");
                $qb->setParameter($filterName, $filterValue);
            }
        }

        //pinned always first
        $qb->addOrderBy('obj.pinned', 'DESC');
        //and then custom sort
        if (!empty($sortBy)) {
            $qb->addOrderBy('obj.'.$sortBy['property'], 1 === $sortBy['direction'] ? 'ASC' : 'DESC');
        }

        return $qb;
    }
}
