<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\BigBlueButtonBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Claroline\BigBlueButtonBundle\Entity\BBB;
use Doctrine\ORM\QueryBuilder;

class BBBFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return BBB::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null): QueryBuilder
    {
        $qb->leftJoin('obj.resourceNode', 'n');
        $qb->andWhere('n.active = true'); // only rooms of non deleted nodes

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'workspace':
                    $qb->leftJoin('n.workspace', 'w');
                    $qb->andWhere('w.uuid = :workspaceId');
                    $qb->setParameter('workspaceId', $filterValue);
                    break;

                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
            }
        }

        return $qb;
    }
}
