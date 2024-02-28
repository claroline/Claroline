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
use Claroline\CommunityBundle\Finder\Filter\UserFilter;
use Claroline\CursusBundle\Entity\EventPresence;
use Doctrine\ORM\QueryBuilder;

class EventPresenceFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return EventPresence::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, ?int $page = 0, ?int $limit = -1): QueryBuilder
    {
        $eventJoin = false;
        $userJoin = false;
        $sessionJoin = false;

        if (!array_key_exists('user', $searches)) {
            $qb->join('obj.user', 'u');
            $userJoin = true;

            // automatically excludes results for disabled/deleted users
            $this->addFilter(UserFilter::class, $qb, 'u', [
                'disabled' => in_array('userDisabled', array_keys($searches)) && $searches['userDisabled'],
            ]);
        }

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'event':
                    if (!$eventJoin) {
                        $qb->join('obj.event', 'e');
                        $eventJoin = true;
                    }

                    $qb->andWhere("e.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;

                case 'user':
                    if (!$userJoin) {
                        $qb->join('obj.user', 'u');
                        $userJoin = true;
                    }

                    $qb->andWhere("u.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;

                case 'session':
                    if (!$eventJoin) {
                        $qb->join('obj.event', 'e');
                        $eventJoin = true;
                    }

                    if (!$sessionJoin) {
                        $qb->join('e.session', 's');
                        $sessionJoin = true;
                    }

                    $qb->andWhere("s.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;

                case 'workspace':
                    if (!$eventJoin) {
                        $qb->join('obj.event', 'e');
                        $eventJoin = true;
                    }

                    if (!$sessionJoin) {
                        $qb->join('e.session', 's');
                        $sessionJoin = true;
                    }

                    $qb->join('s.workspace', 'w');
                    $qb->andWhere("w.uuid = :{$filterName}");

                    $qb->setParameter($filterName, $filterValue);
                    break;

                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
            }
        }

        if (!is_null($sortBy) && isset($sortBy['property']) && isset($sortBy['direction'])) {
            $sortByProperty = $sortBy['property'];
            $sortByDirection = 1 === $sortBy['direction'] ? 'ASC' : 'DESC';

            switch ($sortByProperty) {
                case 'user':
                    if (!$userJoin) {
                        $qb->join('obj.user', 'u');
                    }
                    $qb->orderBy('u.lastName, u.firstName', $sortByDirection);
                    break;

                case 'session':
                    if (!$eventJoin) {
                        $qb->join('obj.event', 'e');
                    }
                    if (!$sessionJoin) {
                        $qb->join('e.session', 's');
                    }
                    $qb->orderBy('s.name', $sortByDirection);
                    break;

                case 'event':
                    if (!$sessionJoin) {
                        $qb->join('e.session', 's');
                    }

                    $qb->orderBy('e.name', $sortByDirection);
                    break;

                case 'startDate':
                    if (!$eventJoin) {
                        $qb->join('obj.event', 'e');
                    }

                    $qb->join('e.plannedObject', 'po');
                    $qb->orderBy('po.startDate', $sortByDirection);
                    break;

                case 'endDate':
                    if (!$eventJoin) {
                        $qb->join('obj.event', 'e');
                    }

                    $qb->join('e.plannedObject', 'po');
                    $qb->orderBy('po.endDate', $sortByDirection);
                    break;
            }
        }

        return $qb;
    }
}
