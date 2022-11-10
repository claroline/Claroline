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
use Claroline\CursusBundle\Entity\Course;
use Doctrine\ORM\QueryBuilder;

class CourseFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return Course::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null): QueryBuilder
    {
        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'slug':
                    // don't use default like on slugs
                    $qb->andWhere("obj.slug = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;

                case 'location':
                    $qb->andWhere("EXISTS (
                        SELECT s.id 
                        FROM Claroline\CursusBundle\Entity\Session AS s
                        LEFT JOIN Claroline\CoreBundle\Entity\Location\Location AS l WITH s.location = l
                        WHERE s.course = obj.id
                          AND l.uuid = :{$filterName}
                    )");
                    $qb->setParameter($filterName, $filterValue);
                    break;

                case 'organizations':
                    $qb->join('obj.organizations', 'o');
                    $qb->andWhere("o.uuid IN (:{$filterName})");
                    $qb->setParameter($filterName, $filterValue);
                    break;

                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
            }
        }

        return $qb;
    }
}
