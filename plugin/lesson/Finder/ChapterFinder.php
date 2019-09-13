<?php

namespace Icap\LessonBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Doctrine\ORM\QueryBuilder;

class ChapterFinder extends AbstractFinder
{
    public function configureQueryBuilder(QueryBuilder $qb, array $searches, array $sortBy = null, array $options = ['count' => false, 'page' => 0, 'limit' => -1])
    {
        return $qb;
    }

    public function getClass()
    {
        return 'Icap\LessonBundle\Entity\Chapter';
    }
}
