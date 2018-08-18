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
        if (!property_exists($this->getClass(), $filterName)) {
            return;
        }

        if (is_bool($filterValue)) {
            $qb->andWhere("obj.{$filterName} = :{$filterName}");
            $qb->setParameter($filterName, $filterValue);
        } else {
            if (is_int($filterValue)) {
                $qb->andWhere("obj.{$filterName} = :{$filterName}");
                $qb->setParameter($filterName, $filterValue);
            } else {
                $qb->andWhere("UPPER(obj.{$filterName}) LIKE :{$filterName}");
                $qb->setParameter($filterName, '%'.strtoupper($filterValue).'%');
            }
        }
    }
}
