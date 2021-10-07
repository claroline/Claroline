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
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Doctrine\ORM\QueryBuilder;

class ResourceUserEvaluationFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return ResourceUserEvaluation::class;
    }

    public function configureQueryBuilder(
        QueryBuilder $qb,
        array $searches = [],
        array $sortBy = null,
        array $options = ['count' => false, 'page' => 0, 'limit' => -1]
    ) {
        $userJoin = false;
        $nodeJoin = false;

        if (!array_key_exists('user', $searches)) {
            // don't show evaluation of disabled/deleted users
            $qb->join('obj.user', 'u');
            $userJoin = true;

            $qb->andWhere('u.isEnabled = TRUE');
            $qb->andWhere('u.isRemoved = FALSE');
        }

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'user':
                    if (!$userJoin) {
                        $qb->join('obj.user', 'u');
                        $userJoin = true;
                    }
                    $qb->andWhere("u.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'user.firstName':
                    if (!$userJoin) {
                        $qb->join('obj.user', 'u');
                        $userJoin = true;
                    }
                    $qb->andWhere('UPPER(u.firstName) LIKE :firstName');
                    $qb->setParameter('firstName', '%'.strtoupper($filterValue).'%');
                    break;
                case 'user.lastName':
                    if (!$userJoin) {
                        $qb->join('obj.user', 'u');
                        $userJoin = true;
                    }
                    $qb->andWhere('UPPER(u.lastName) LIKE :lastName');
                    $qb->setParameter('lastName', '%'.strtoupper($filterValue).'%');
                    break;
                case 'resourceNode':
                    if (!$nodeJoin) {
                        $qb->join('obj.resourceNode', 'r');
                        $nodeJoin = true;
                    }

                    $qb->andWhere("r.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'workspace':
                    if (!$nodeJoin) {
                        $qb->join('obj.resourceNode', 'r');
                        $nodeJoin = true;
                    }

                    $qb->join('r.workspace', 'w');
                    $qb->andWhere("w.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'fromDate':
                    $qb->andWhere("obj.date >= :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'untilDate':
                    $qb->andWhere("obj.date <= :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'progression':
                    if ($filterValue) {
                        $qb->andWhere("obj.progression = :{$filterName}");
                        $qb->setParameter($filterName, $filterValue);
                    } else {
                        $qb->andWhere(
                            $qb->expr()->orX(
                                'obj.progression IS NULL',
                                'obj.progression = 0'
                            )
                        );
                    }
                    break;
                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
                    break;
            }
        }

        if (!is_null($sortBy) && isset($sortBy['property']) && isset($sortBy['direction'])) {
            $sortByProperty = $sortBy['property'];
            $sortByDirection = 1 === $sortBy['direction'] ? 'ASC' : 'DESC';

            switch ($sortByProperty) {
                case 'user.firstName':
                    if (!$userJoin) {
                        $qb->join('obj.user', 'u');
                    }
                    $qb->orderBy('u.firstName', $sortByDirection);
                    break;
                case 'user.lastName':
                    if (!$userJoin) {
                        $qb->join('obj.user', 'u');
                    }
                    $qb->orderBy('u.lastName', $sortByDirection);
                    break;
            }
        }

        return $qb;
    }
}
