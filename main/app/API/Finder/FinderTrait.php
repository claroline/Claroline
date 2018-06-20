<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AppBundle\API\Finder;

use Doctrine\ORM\QueryBuilder;

trait FinderTrait
{
    public function setDefaults(QueryBuilder $qb, $filterName, $filterValue)
    {
        if (is_string($filterValue)) {
            $qb->andWhere("UPPER(obj.{$filterName}) LIKE :{$filterName}");
            $qb->setParameter($filterName, '%'.strtoupper($filterValue).'%');
        } else {
            $qb->andWhere("obj.{$filterName} = :{$filterName}");
            $qb->setParameter($filterName, $filterValue);
        }

        return $qb;
    }
}
