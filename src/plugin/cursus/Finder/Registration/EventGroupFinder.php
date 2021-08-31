<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Finder\Registration;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Claroline\CursusBundle\Entity\Registration\EventGroup;
use Doctrine\ORM\QueryBuilder;

class EventGroupFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return EventGroup::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, array $options = ['count' => false, 'page' => 0, 'limit' => -1])
    {
        $groupJoin = false;
        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'event':
                    $qb->join('obj.event', 'e');
                    $qb->andWhere("e.uuid = :{$filterName}");
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

                case 'user':
                    if (!$groupJoin) {
                        $qb->join('obj.group', 'g');
                        $groupJoin = true;
                    }

                    $qb->leftJoin('g.users', 'gu');
                    $qb->andWhere("gu.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;

                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
            }
        }

        return $qb;
    }
}
