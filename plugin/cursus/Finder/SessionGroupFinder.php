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
use Claroline\CursusBundle\Entity\CourseSessionGroup;
use Doctrine\ORM\QueryBuilder;

class SessionGroupFinder extends AbstractFinder
{
    public function getClass()
    {
        return CourseSessionGroup::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, array $options = ['count' => false, 'page' => 0, 'limit' => -1])
    {
        $groupJoin = false;

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'session':
                    $qb->join('obj.session', 's');
                    $qb->andWhere("s.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'group':
                    if (!$groupJoin) {
                        $qb->join('obj.group', 'g');
                        $groupJoin = true;
                    }
                    $qb->andWhere("g.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'group.name':
                    if (!$groupJoin) {
                        $qb->join('obj.group', 'g');
                        $groupJoin = true;
                    }
                    $qb->andWhere('g.name LIKE :groupName');
                    $qb->setParameter('groupName', '%'.strtoupper($filterValue).'%');
                    break;
                case 'type':
                    $qb->andWhere("obj.groupType = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
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
                case 'group.name':
                    if (!$groupJoin) {
                        $qb->join('obj.group', 'g');
                    }
                    $qb->orderBy('g.name', $sortByDirection);
                    break;
            }
        }

        return $qb;
    }
}
