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
use Claroline\BigBlueButtonBundle\Entity\Recording;
use Doctrine\ORM\QueryBuilder;

class RecordingFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return Recording::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, array $options = ['count' => false, 'page' => 0, 'limit' => -1])
    {
        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'workspace':
                    $qb->leftJoin('obj.meeting', 'm');
                    $qb->leftJoin('m.resourceNode', 'n');
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
