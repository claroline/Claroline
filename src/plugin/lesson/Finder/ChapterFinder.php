<?php

namespace Icap\LessonBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Doctrine\ORM\QueryBuilder;
use Icap\LessonBundle\Entity\Chapter;

class ChapterFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return Chapter::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null): QueryBuilder
    {
        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'lesson':
                    $qb->join('obj.lesson', 'l');
                    $qb->andWhere("l.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;

                case 'content':
                    $values = explode('+', $filterValue);
                    foreach ($values as $index => $value) {
                        $qb->andWhere("(UPPER(obj.title) LIKE :content{$index} OR UPPER(obj.text) LIKE :content{$index})");
                        $qb->setParameter('content'.$index, '%'.strtoupper($value).'%');
                    }
                    break;

                case 'contentAndNote':
                    $values = explode('+', $filterValue);
                    foreach ($values as $index => $value) {
                        $qb->andWhere("(UPPER(obj.title) LIKE :content{$index} OR UPPER(obj.text) LIKE :content{$index} OR UPPER(obj.internalNote) LIKE :content{$index})");
                        $qb->setParameter('content'.$index, '%'.strtoupper($value).'%');
                    }
                    break;

                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
            }
        }

        return $qb;
    }
}
