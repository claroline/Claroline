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
use Claroline\CoreBundle\Entity\Resource\ResourceEvaluation;
use Doctrine\ORM\QueryBuilder;

class ResourceEvaluationFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return ResourceEvaluation::class;
    }

    public function configureQueryBuilder(
        QueryBuilder $qb,
        array $searches = [],
        array $sortBy = null,
        array $options = ['count' => false, 'page' => 0, 'limit' => -1]
    ) {
        $userJoin = false;
        $nodeJoin = false;

        $qb->join('obj.resourceUserEvaluation', 'ru');

        if (!array_key_exists('user', $searches)) {
            // don't show evaluation of disabled/deleted users
            $qb->join('ru.user', 'u');
            $userJoin = true;

            $qb->andWhere('u.isEnabled = TRUE');
            $qb->andWhere('u.isRemoved = FALSE');
        }

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'user':
                    if (!$userJoin) {
                        $qb->join('ru.user', 'u');
                        $userJoin = true;
                    }
                    $qb->andWhere("u.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'resourceNode':
                    if (!$nodeJoin) {
                        $qb->join('ru.resourceNode', 'r');
                        $nodeJoin = true;
                    }

                    $qb->andWhere("r.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'workspace':
                    if (!$nodeJoin) {
                        $qb->join('ru.resourceNode', 'r');
                        $nodeJoin = true;
                    }

                    $qb->join('r.workspace', 'w');
                    $qb->andWhere("w.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
                    break;
            }
        }

        return $qb;
    }
}
