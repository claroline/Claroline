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
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

class EventFinder extends AbstractFinder
{
    public function getClass()
    {
        return Event::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, array $options = ['count' => false, 'page' => 0, 'limit' => -1])
    {
        $workspaceJoin = false;

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'workspaces':
                    if (!$workspaceJoin) {
                        $qb->leftJoin('obj.workspace', 'w');
                        $workspaceJoin = true;
                    }
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
                case 'inRange':
                    if (!empty($filterValue[0])) {
                        $qb->andWhere('obj.start >= :startRange OR obj.end >= :startRange2');
                        $qb->setParameter('startRange', $filterValue[0]);
                        $qb->setParameter('startRange2', $filterValue[0]);
                    }

                    if (!empty($filterValue[1])) {
                        $qb->andWhere('obj.start <= :endRange OR obj.end <= :endRange2');
                        $qb->setParameter('endRange', $filterValue[1]);
                        $qb->setParameter('endRange2', $filterValue[1]);
                    }

                    break;
                case 'afterToday':
                    if ($filterValue) {
                        $qb->andWhere("obj.start >= :{$filterName}");
                        $qb->setParameter($filterName, new \DateTime());
                    }
                    break;
                case 'userId':
                    $qb->leftJoin('obj.creator', 'u');
                    $qb->andWhere("u.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
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
                    if (!$workspaceJoin) {
                        $qb->leftJoin('obj.workspace', 'w', Join::WITH, 'w.displayable = true AND w.model = false AND w.personal = false');
                        $workspaceJoin = true;
                    }

                    // join for creator
                    $qb->leftJoin('obj.creator', 'u');

                    // join for tool rights
                    $qb->leftJoin('w.orderedTools', 'ot');
                    $qb->leftJoin('ot.tool', 'ott');
                    $qb->leftJoin('ot.rights', 'otr');
                    $qb->leftJoin('otr.role', 'otrr');
                    $qb->leftJoin('otrr.users', 'otrru');
                    // join for workspace manager role
                    $qb->leftJoin('w.roles', 'wr');
                    $qb->leftJoin('wr.users', 'wru');

                    $qb->andWhere($qb->expr()->orX(
                        $qb->expr()->eq('u.uuid', ':userId'),
                        $qb->expr()->andX(
                            $qb->expr()->eq('ott.name', ':agenda'),
                            $qb->expr()->eq('otrru.uuid', ':roleUserId'),
                            $qb->expr()->eq('BIT_AND(otr.mask, 1)', '1')
                        ),
                        $qb->expr()->andX(
                            $qb->expr()->eq('wr.name', 'CONCAT(:managerRolePrefix, w.uuid)'),
                            $qb->expr()->eq('wru.uuid', ':managerId')
                        )
                    ));

                    $qb->setParameter('userId', $filterValue);
                    $qb->setParameter('agenda', 'agenda');
                    $qb->setParameter('roleUserId', $filterValue);
                    $qb->setParameter('managerRolePrefix', 'ROLE_WS_MANAGER_');
                    $qb->setParameter('managerId', $filterValue);

                    break;
                case '_group':
                    if (!$workspaceJoin) {
                        $qb->leftJoin('obj.workspace', 'w', Join::WITH, 'w.displayable = true AND w.model = false AND w.personal = false');
                        $workspaceJoin = true;
                    }

                    // join for tool rights
                    $qb->leftJoin('w.orderedTools', 'ot');
                    $qb->leftJoin('ot.tool', 'ott');
                    $qb->leftJoin('ot.rights', 'otr');
                    $qb->leftJoin('otr.role', 'otrr');
                    $qb->leftJoin('otrr.groups', 'otrrg');
                    $qb->leftJoin('otrrg.users', 'otrru');

                    // join for workspace manager role
                    $qb->leftJoin('w.roles', 'r');
                    $qb->leftJoin('r.groups', 'rg');
                    $qb->leftJoin('rg.users', 'rgu');

                    $qb->andWhere($qb->expr()->orX(
                        $qb->expr()->andX(
                            $qb->expr()->eq('ott.name', ':agenda'),
                            $qb->expr()->eq('otrru.uuid', ':_groupUserId'),
                            $qb->expr()->eq('BIT_AND(otr.mask, 1)', '1')
                        ),
                        $qb->expr()->andX(
                            $qb->expr()->eq('r.name', 'CONCAT(:_managerRolePrefix, w.uuid)'),
                            $qb->expr()->eq('rgu.uuid', ':_managerId')
                        )
                    ));

                    $qb->setParameter('agenda', 'agenda');
                    $qb->setParameter('_groupUserId', $filterValue);
                    $qb->setParameter('_managerId', $filterValue);
                    $qb->setParameter('_managerRolePrefix', 'ROLE_WS_MANAGER_');
                    break;
                case 'anonymous':
                    if (!$workspaceJoin) {
                        $qb->leftJoin('obj.workspace', 'w', Join::WITH, 'w.displayable = true AND w.model = false AND w.personal = false');
                        $workspaceJoin = true;
                    }
                    $qb->join('w.orderedTools', 'ot');
                    $qb->join('ot.tool', 'ott');
                    $qb->join('ot.rights', 'otr');
                    $qb->join('otr.role', 'otrr');
                    $qb->andWhere("ott.name = 'agenda'");
                    $qb->andWhere("otrr.name = 'ROLE_ANONYMOUS'");
                    $qb->andWhere('BIT_AND(otr.mask, 1) = 1');
                    break;
                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
           }
        }

        $qb->andWhere($qb->expr()->gte('obj.end', 'obj.start'));

        return $qb;
    }
}
