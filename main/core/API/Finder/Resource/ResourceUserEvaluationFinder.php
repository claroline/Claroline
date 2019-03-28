<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\API\Finder\Resource;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Doctrine\ORM\QueryBuilder;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.api.finder.resource_user_evaluation")
 * @DI\Tag("claroline.finder")
 */
class ResourceUserEvaluationFinder extends AbstractFinder
{
    public function getClass()
    {
        return ResourceUserEvaluation::class;
    }

    public function configureQueryBuilder(
        QueryBuilder $qb,
        array $searches = [],
        array $sortBy = null,
        array $options = ['count' => false, 'page' => 0, 'limit' => -1]
    ) {
        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'user':
                    $qb->join('obj.user', 'u');
                    $qb->andWhere("u.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'resourceNode':
                    $qb->join('obj.resourceNode', 'r');
                    $qb->andWhere("r.uuid = :{$filterName}");
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

        return $qb;
    }
}
