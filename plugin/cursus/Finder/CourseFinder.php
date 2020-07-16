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
    public function getClass()
    {
        return Course::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, array $options = ['count' => false, 'page' => 0, 'limit' => -1])
    {
        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'available':
                    $qb->leftJoin('obj.sessions', 's');
                    $qb->andWhere('s.id IS NOT NULL');
                    $qb->andWhere('s.endDate > :now');
                    $qb->setParameter('now', new \DateTime());

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

    public function getExtraFieldMapping()
    {
        return [
            'name' => 'title',
        ];
    }
}
