<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\EvaluationBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Claroline\CoreBundle\Entity\Workspace\Evaluation;
use Doctrine\ORM\QueryBuilder;

class WorkspaceEvaluationFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return Evaluation::class;
    }

    public function configureQueryBuilder(
        QueryBuilder $qb,
        array $searches = [],
        array $sortBy = null,
        array $options = ['count' => false, 'page' => 0, 'limit' => -1]
    ) {
        $userJoin = false;
        if (!array_key_exists('user', $searches)) {
            // don't show evaluation of disabled/deleted users
            $qb->join('obj.user', 'u');
            $userJoin = true;

            $qb->andWhere('u.isEnabled = TRUE');
            $qb->andWhere('u.isRemoved = FALSE');
        }

        $workspaceJoin = false;
        if (!array_key_exists('workspace', $searches) && !array_key_exists('workspaces', $searches)) {
            // don't show evaluation of archived workspaces
            $qb->join('obj.workspace', 'w');
            $workspaceJoin = true;

            $qb->andWhere('w.archived = FALSE');
        }

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'workspace':
                    $qb->join('obj.workspace', 'w');
                    $workspaceJoin = true;

                    $qb->andWhere("w.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'workspaces':
                    $qb->join('obj.workspace', 'w');
                    $workspaceJoin = true;

                    $qb->andWhere("w.uuid IN (:{$filterName})");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'user':
                    $qb->join('obj.user', 'u');
                    $userJoin = true;

                    $qb->andWhere("u.uuid = :{$filterName}");
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
                    $qb->orderBy('u.lastName', $sortByDirection);
                    break;
                case 'workspace':
                    if (!$workspaceJoin) {
                        $qb->join('obj.workspace', 'w');
                    }
                    $qb->orderBy('w.name', $sortByDirection);
                    break;
            }
        }

        return $qb;
    }
}
