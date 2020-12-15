<?php

namespace Icap\LessonBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Doctrine\ORM\QueryBuilder;
use Icap\LessonBundle\Entity\Chapter;

class ChapterFinder extends AbstractFinder
{
    public function getClass()
    {
        return Chapter::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches, array $sortBy = null, array $options = ['count' => false, 'page' => 0, 'limit' => -1])
    {
        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'lesson':
                    $qb->join('obj.lesson', 'l');
                    $qb->andWhere("l.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;

                case 'content':
                    $qb->andWhere("(UPPER(obj.title) LIKE :{$filterName} OR UPPER(obj.text) LIKE :{$filterName})");
                    $qb->setParameter($filterName, '%'.strtoupper($filterValue).'%');
                    break;

                case 'contentAndNote':
                    $qb->andWhere("(UPPER(obj.title) LIKE :{$filterName} OR UPPER(obj.text) LIKE :{$filterName} OR UPPER(obj.internalNote) LIKE :{$filterName})");
                    $qb->setParameter($filterName, '%'.strtoupper($filterValue).'%');
                    break;

                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
            }
        }

        return $qb;
    }
}
