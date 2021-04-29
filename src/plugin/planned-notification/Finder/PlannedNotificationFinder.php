<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\PlannedNotificationBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Doctrine\ORM\QueryBuilder;

class PlannedNotificationFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return 'Claroline\PlannedNotificationBundle\Entity\PlannedNotification';
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, array $options = ['count' => false, 'page' => 0, 'limit' => -1])
    {
        $rolesJoin = false;
        $qb->join('obj.workspace', 'w');
        $qb->andWhere('w.uuid = :workspaceUuid');
        $qb->setParameter('workspaceUuid', $searches['workspace']);

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'workspace':
                    break;
                case 'roles':
                    $qb->leftJoin('obj.roles', 'r');
                    $qb->andWhere("UPPER(r.translationKey) LIKE :{$filterName}");
                    $qb->setParameter($filterName, '%'.strtoupper($filterValue).'%');
                    $rolesJoin = true;
                    break;
                case 'message.title':
                    $qb->join('obj.message', 'm');
                    $qb->andWhere('UPPER(m.title) LIKE :messageTitle');
                    $qb->setParameter('messageTitle', '%'.strtoupper($filterValue).'%');
                    break;
                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
            }
        }
        if (!is_null($sortBy) && isset($sortBy['property']) && isset($sortBy['direction'])) {
            $sortByProperty = $sortBy['property'];
            $sortByDirection = 1 === $sortBy['direction'] ? 'ASC' : 'DESC';

            switch ($sortByProperty) {
                case 'roles':
                    if (!$rolesJoin) {
                        $qb->leftJoin('obj.roles', 'r');
                    }
                    $qb->orderBy('r.translationKey', $sortByDirection);
                    break;
            }
        }

        return $qb;
    }
}
