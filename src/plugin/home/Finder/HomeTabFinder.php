<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\HomeBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Claroline\HomeBundle\Entity\HomeTab;
use Doctrine\ORM\QueryBuilder;

class HomeTabFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return HomeTab::class;
    }

    public function configureQueryBuilder(
        QueryBuilder $qb,
        array $searches = [],
        array $sortBy = null,
        array $options = ['count' => false, 'page' => 0, 'limit' => -1]
    ) {
        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'context':
                    $qb->andWhere("obj.context = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);

                    break;
                case 'user':
                    $qb->leftJoin('obj.user', 'u');
                    $qb->andWhere("u.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);

                    break;
                case 'workspace':
                    $qb->leftJoin('obj.workspace', 'w');
                    $qb->andWhere("w.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);

                    break;
                case 'parent':
                    if (empty($filterValue)) {
                        $qb->andWhere('obj.parent IS NULL');
                    } else {
                        $qb->andWhere("obj.parent = :{$filterName}");
                        $qb->setParameter($filterName, $filterValue);
                    }

                    break;
                case 'roles':
                    if (!empty($filterValue)) {
                        $qb->leftJoin('obj.roles', 'r');
                        $qb->andWhere('(r.id IS NULL OR r.name IN (:roles))');
                        $qb->setParameter('roles', $filterValue);
                    }

                    break;
                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
            }
        }

        $qb->orderBy('obj.order', 'ASC');

        return $qb;
    }
}
