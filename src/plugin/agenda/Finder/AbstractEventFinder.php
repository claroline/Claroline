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

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

abstract class AbstractEventFinder extends AbstractFinder
{
    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, array $options = ['count' => false, 'page' => 0, 'limit' => -1])
    {
        $qb->leftJoin('obj.plannedObject', 'p');
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
                case 'inRange':
                    if (!empty($filterValue[0])) {
                        $qb->andWhere('p.startDate >= :startRange OR p.endDate >= :startRange2');
                        $qb->setParameter('startRange', $filterValue[0]);
                        $qb->setParameter('startRange2', $filterValue[0]);
                    }

                    if (!empty($filterValue[1])) {
                        $qb->andWhere('p.startDate <= :endRange OR p.endDate <= :endRange2');
                        $qb->setParameter('endRange', $filterValue[1]);
                        $qb->setParameter('endRange2', $filterValue[1]);
                    }

                    break;
                case 'afterToday':
                    if ($filterValue) {
                        $qb->andWhere("p.startDate >= :{$filterName}");
                        $qb->setParameter($filterName, new \DateTime());
                    }
                    break;
                case 'userId':
                    $qb->leftJoin('p.creator', 'u');
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
                    $qb->leftJoin('p.creator', 'u');

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

                // map search on PlannedObject (There may be a better way to handle this).
                case 'name':
                case 'description':
                case 'startDate':
                case 'endDate':
                    $qb->andWhere("UPPER(p.{$filterName}) LIKE :{$filterName}");
                    $qb->setParameter($filterName, '%'.strtoupper($filterValue).'%');
                    break;

                case 'location':
                    $qb->join('po.location', 'l');
                    $qb->andWhere("l.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;

                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
           }
        }

        $qb->andWhere($qb->expr()->gte('p.endDate', 'p.startDate'));

        return $qb;
    }
}
