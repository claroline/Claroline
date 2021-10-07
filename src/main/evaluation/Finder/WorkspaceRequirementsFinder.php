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
use Claroline\CoreBundle\Entity\Workspace\Requirements;
use Doctrine\ORM\QueryBuilder;

class WorkspaceRequirementsFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return Requirements::class;
    }

    public function configureQueryBuilder(
        QueryBuilder $qb,
        array $searches = [],
        array $sortBy = null,
        array $options = ['count' => false, 'page' => 0, 'limit' => -1]
    ) {
        $roleJoin = false;
        $userJoin = false;

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'workspace':
                    $qb->join('obj.workspace', 'w');
                    $qb->andWhere("w.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'role':
                    if (!$roleJoin) {
                        $qb->join('obj.role', 'r');
                        $roleJoin = true;
                    }
                    $qb->andWhere("r.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'role.translationKey':
                    if (!$roleJoin) {
                        $qb->join('obj.role', 'r');
                        $roleJoin = true;
                    }
                    $qb->andWhere('UPPER(r.translationKey) LIKE :translationKey');
                    $qb->setParameter('translationKey', '%'.strtoupper($filterValue).'%');
                    break;
                case 'user':
                    if (!$userJoin) {
                        $qb->join('obj.user', 'u');
                        $userJoin = true;
                    }
                    $qb->andWhere("u.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'userName':
                    if (!$userJoin) {
                        $qb->join('obj.user', 'u');
                        $userJoin = true;
                    }
                    $qb->andWhere($qb->expr()->orX(
                        $qb->expr()->like(
                            "CONCAT(CONCAT(UPPER(u.firstName), ' '), UPPER(u.lastName))",
                            ':userName'
                        ),
                        $qb->expr()->like(
                            "CONCAT(CONCAT(UPPER(u.lastName), ' '), UPPER(u.firstName))",
                            ':userName'
                        )
                    ));
                    $qb->setParameter('userName', '%'.strtoupper($filterValue).'%');
                    break;
                case 'withRole':
                    if (!$roleJoin) {
                        $qb->join('obj.role', 'r');
                        $roleJoin = true;
                    }
                    break;
                case 'withUser':
                    if (!$userJoin) {
                        $qb->join('obj.user', 'u');
                        $userJoin = true;
                    }
                    break;
                case 'resource':
                    $qb->join('obj.resources', 'res');
                    $qb->andWhere("res.uuid = :{$filterName}");
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
                case 'role.translationKey':
                    if (!$roleJoin) {
                        $qb->join('obj.role', 'r');
                    }
                    $qb->orderBy('r.translationKey', $sortByDirection);
                    break;
                case 'userName':
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
