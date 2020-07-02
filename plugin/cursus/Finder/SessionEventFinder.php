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
use Claroline\CursusBundle\Entity\SessionEvent;
use Doctrine\ORM\QueryBuilder;

class SessionEventFinder extends AbstractFinder
{
    public function getClass()
    {
        return SessionEvent::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, array $options = ['count' => false, 'page' => 0, 'limit' => -1])
    {
        $qb->join('obj.session', 's');
        $qb->join('s.course', 'c');
        $eventSetJoin = false;

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'organizations':
                    $qb->join('c.organizations', 'o');
                    $qb->andWhere("o.uuid IN (:{$filterName})");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'session':
                    $qb->andWhere("s.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'sessionName':
                    $qb->andWhere("UPPER(s.name) LIKE :{$filterName}");
                    $qb->setParameter($filterName, '%'.strtoupper($filterValue).'%');
                    break;
                case 'course':
                    $qb->andWhere("c.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'courseTitle':
                    $qb->andWhere("UPPER(c.title) LIKE :{$filterName}");
                    $qb->setParameter($filterName, '%'.strtoupper($filterValue).'%');
                    break;
                case 'eventSet':
                    $qb->join('obj.eventSet', 'es');
                    $qb->andWhere("UPPER(es.name) LIKE :{$filterName}");
                    $qb->setParameter($filterName, '%'.strtoupper($filterValue).'%');
                    $eventSetJoin = true;
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
                case 'session':
                case 'sessionName':
                    $qb->orderBy('s.name', $sortByDirection);
                    break;
                case 'course':
                case 'courseTitle':
                    $qb->orderBy('c.title', $sortByDirection);
                    break;
                case 'eventSet':
                    if (!$eventSetJoin) {
                        $qb->join('obj.eventSet', 'es');
                    }
                    $qb->orderBy('es.name', $sortByDirection);
                    break;
            }
        }

        return $qb;
    }
}
