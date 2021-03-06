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
use Claroline\ThemeBundle\Entity\Icon\IconSet;
use Doctrine\ORM\QueryBuilder;

class IconSetFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return IconSet::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, array $options = ['count' => false, 'page' => 0, 'limit' => -1])
    {
        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
                    break;
            }
        }

        return $qb;
    }
}
