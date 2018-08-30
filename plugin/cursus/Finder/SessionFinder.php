<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Doctrine\ORM\QueryBuilder;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.api.finder.cursus.session")
 * @DI\Tag("claroline.finder")
 */
class SessionFinder extends AbstractFinder
{
    public function getClass()
    {
        return 'Claroline\CursusBundle\Entity\CourseSession';
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null)
    {
        $qb->join('obj.course', 'c');
        $qb->join('c.organizations', 'o');
        $qb->andWhere('o.uuid IN (:organizations)');
        $qb->setParameter('organizations', $searches['organizations']);

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'organizations':
                    break;
                case 'course':
                    $qb->andWhere("c.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'courseTitle':
                    $qb->andWhere("UPPER(c.title) LIKE :{$filterName}");
                    $qb->setParameter($filterName, '%'.strtoupper($filterValue).'%');
                    break;
                default:
                    if (is_bool($filterValue)) {
                        $qb->andWhere("obj.{$filterName} = :{$filterName}");
                        $qb->setParameter($filterName, $filterValue);
                    } else {
                        $qb->andWhere("UPPER(obj.{$filterName}) LIKE :{$filterName}");
                        $qb->setParameter($filterName, '%'.strtoupper($filterValue).'%');
                    }
            }
        }
        if (!is_null($sortBy) && isset($sortBy['property']) && isset($sortBy['direction'])) {
            $sortByProperty = $sortBy['property'];
            $sortByDirection = 1 === $sortBy['direction'] ? 'ASC' : 'DESC';

            switch ($sortByProperty) {
                case 'course':
                case 'courseTitle':
                    $qb->orderBy('c.title', $sortByDirection);
                    break;
            }
        }

        return $qb;
    }
}
