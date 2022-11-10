<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ThemeBundle\Finder\Icon;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Claroline\ThemeBundle\Entity\Icon\IconItem;
use Doctrine\ORM\QueryBuilder;

class IconItemFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return IconItem::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null): QueryBuilder
    {
        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'iconSet':
                    $qb->join('obj.iconSet', 's');
                    $qb->andWhere("s.uuid = :{$filterName}");
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
