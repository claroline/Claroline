<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AgendaBundle\Finder;

use Claroline\AgendaBundle\Entity\Event;
use Claroline\AppBundle\API\Finder\AbstractFinder;
use Doctrine\ORM\QueryBuilder;

class EventFinder extends AbstractFinder
{
    public function getClass()
    {
        return Event::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, array $options = ['count' => false, 'page' => 0, 'limit' => -1])
    {
        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'workspaces':
                    $qb->leftJoin('obj.workspace', 'w');
                    $qb->andWhere('w.uuid IN (:'.$filterName.')');
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'types':
                    if (1 === count($filterValue)) {
                        if ('task' === $filterValue[0]) {
                            $qb->andWhere('obj.isTask = true');
                        } elseif ('event' === $filterValue[0]) {
                            $qb->andWhere('obj.isTask = false');
                        }
                    }

                    break;
                case 'createdBefore':
                    $qb->andWhere("obj.start <= :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'createdAfter':
                    $qb->andWhere("obj.start >= :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'endBefore':
                    $qb->andWhere("obj.end <= :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'endAfter':
                    $qb->andWhere("obj.end >= :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'notDoneYet':
                    //includes today
                    $now = new \DateTime();
                    $interval = new \DateInterval('P1D');
                    $now->sub($interval);
                    if ($filterValue) {
                        $qb->andWhere("obj.start >= :{$filterName}");
                    } else {
                        $qb->andWhere("obj.start <= :{$filterName}");
                    }
                    $qb->setParameter($filterName, $now);
                    break;
                case 'user':
                    $byUserSearch = $byGroupSearch = $searches;
                    $byUserSearch['_user'] = $filterValue;
                    $byGroupSearch['_group'] = $filterValue;
                    unset($byUserSearch['user']);
                    unset($byGroupSearch['user']);

                    return $this->union($byUserSearch, $byGroupSearch, $options, $sortBy);
                    break;
                case '_user':
                    $qb->join('obj.workspace', 'w');
                    $qb->leftJoin('w.roles', 'r');
                    $qb->leftJoin('r.users', 'ru');
                    $qb->andWhere($qb->expr()->orX(
                        $qb->expr()->like('ru.id', ':_userId'),
                        $qb->expr()->like('ru.uuid', ':_userUuid')
                    ));
                    $qb->andWhere('r.name != :roleUser');
                    $qb->setParameter('_userId', $filterValue);
                    $qb->setParameter('_userUuid', $filterValue);
                    $qb->setParameter('roleUser', 'ROLE_USER');

                    break;
                case '_group':
                    $qb->join('obj.workspace', 'w');
                    $qb->leftJoin('w.roles', 'r');
                    $qb->leftJoin('r.groups', 'rg');
                    $qb->leftJoin('rg.users', 'rgu');
                    $qb->andWhere($qb->expr()->orX(
                        $qb->expr()->like('rgu.id', ':_groupUserId'),
                        $qb->expr()->like('rgu.uuid', ':_groupUserUuid')
                    ));
                    $qb->andWhere('r.name != :roleUser');
                    $qb->setParameter('_groupUserId', $filterValue);
                    $qb->setParameter('_groupUserUuid', $filterValue);
                    $qb->setParameter('roleUser', 'ROLE_USER');
                    break;
                case 'anonymous':
                    $qb->join('obj.workspace', 'w');
                    $qb->join('w.orderedTools', 'ot');
                    $qb->join('ot.tool', 'ott');
                    $qb->join('ot.rights', 'otr');
                    $qb->join('otr.role', 'otrr');
                    $qb->andWhere('w.displayable = true');
                    $qb->andWhere('w.model = false');
                    $qb->andWhere('w.personal = false');
                    $qb->andWhere("ott.name = 'agenda'");
                    $qb->andWhere("otrr.name = 'ROLE_ANONYMOUS'");
                    $qb->andWhere('BIT_AND(otr.mask, 1) = 1');
                    break;
                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
           }
        }

        return $qb;
    }
}
