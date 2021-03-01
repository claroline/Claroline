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
use Claroline\CursusBundle\Entity\Event;
use Doctrine\ORM\QueryBuilder;

class EventFinder extends AbstractFinder
{
    public function getClass()
    {
        return Event::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, array $options = ['count' => false, 'page' => 0, 'limit' => -1])
    {
        $qb->join('obj.session', 's');
        $qb->join('s.course', 'c');

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'terminated':
                    if ($filterValue) {
                        $qb->andWhere('obj.endDate < :endDate');
                    } else {
                        $qb->andWhere($qb->expr()->orX(
                            $qb->expr()->isNull('obj.endDate'),
                            $qb->expr()->gte('obj.endDate', ':endDate')
                        ));
                    }
                    $qb->setParameter('endDate', new \DateTime());
                    break;

                case 'organizations':
                    $qb->join('c.organizations', 'o');
                    $qb->andWhere("o.uuid IN (:{$filterName})");
                    $qb->setParameter($filterName, $filterValue);
                    break;

                case 'session':
                    $qb->andWhere("s.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;

                case 'workspace':
                    $qb->join('s.workspace', 'w');
                    $qb->andWhere("w.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;

                case 'course':
                    $qb->andWhere("c.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;

                case 'location':
                    $qb->join('obj.location', 'l');
                    $qb->andWhere("l.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;

                case 'user':
                    $qb->leftJoin('Claroline\CursusBundle\Entity\Registration\EventUser', 'eu', 'WITH', 'eu.event = obj');
                    $qb->leftJoin('eu.user', 'u');
                    $qb->leftJoin('Claroline\CursusBundle\Entity\Registration\EventGroup', 'eg', 'WITH', 'eg.event = obj');
                    $qb->leftJoin('eg.group', 'g');
                    $qb->leftJoin('g.users', 'gu');
                    $qb->andWhere('eu.confirmed = 1 AND eu.validated = 1');
                    $qb->andWhere($qb->expr()->orX(
                        $qb->expr()->eq('u.uuid', ':userId'),
                        $qb->expr()->eq('gu.uuid', ':userId')
                    ));
                    $qb->setParameter('userId', $filterValue);
                    break;

                case 'userPending':
                    $qb->leftJoin('Claroline\CursusBundle\Entity\Registration\EventUser', 'eu', 'WITH', 'eu.event = obj');
                    $qb->leftJoin('eu.user', 'u');
                    $qb->andWhere('(eu.confirmed = 0 AND eu.validated = 0)');
                    $qb->andWhere('u.uuid = :userId');
                    $qb->setParameter('userId', $filterValue);
                    break;

                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
            }
        }

        return $qb;
    }
}
