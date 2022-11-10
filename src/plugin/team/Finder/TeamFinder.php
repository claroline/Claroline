<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\TeamBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Claroline\TeamBundle\Entity\Team;
use Doctrine\ORM\QueryBuilder;

class TeamFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return Team::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null): QueryBuilder
    {
        $managerJoin = false;

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'workspace':
                    $qb->join('obj.workspace', 'w');
                    $qb->andWhere($qb->expr()->orX( // TODO : should only manage one. Can have false positive because of trans-typing
                        $qb->expr()->like('w.id', ':workspaceId'),
                        $qb->expr()->like('w.uuid', ':workspaceId')
                    ));
                    $qb->setParameter('workspaceId', $searches['workspace']);
                    break;
                case 'teamManager':
                    $where = "CONCAT(UPPER(m.firstName), CONCAT(' ', UPPER(m.lastName))) LIKE :{$filterName}";
                    $where .= " OR CONCAT(UPPER(m.lastName), CONCAT(' ', UPPER(m.firstName))) LIKE :{$filterName}";
                    $qb->join('obj.teamManager', 'm');
                    $qb->andWhere($where);
                    $qb->setParameter($filterName, '%'.strtoupper($filterValue).'%');
                    $managerJoin = true;
                    break;
                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
            }
        }
        if (!is_null($sortBy) && isset($sortBy['property']) && isset($sortBy['direction'])) {
            $sortByProperty = $sortBy['property'];
            $sortByDirection = 1 === $sortBy['direction'] ? 'ASC' : 'DESC';

            switch ($sortByProperty) {
                case 'teamManager':
                    if (!$managerJoin) {
                        $qb->join('obj.teamManager', 'm');
                    }
                    $qb->orderBy('m.lastName', $sortByDirection);
                    break;
                case 'countUsers':
                    $qb->select('obj, COUNT(u.id) AS HIDDEN mycount');
                    $qb->leftJoin('obj.role', 'r');
                    $qb->leftJoin('r.users', 'u');
                    $qb->groupBy('r.id');
                    $qb->orderBy('mycount', $sortByDirection);
                    break;
            }
        }

        return $qb;
    }
}
