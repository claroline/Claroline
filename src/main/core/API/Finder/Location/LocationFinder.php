<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\API\Finder\Location;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Claroline\CoreBundle\Entity\Location\Location;
use Doctrine\ORM\QueryBuilder;

class LocationFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return Location::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null): QueryBuilder
    {
        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'organizations':
                    $qb->join('obj.organizations', 'o');
                    $qb->andWhere('o.uuid IN (:organizationIds)');
                    $qb->setParameter('organizationIds', is_array($filterValue) ? $filterValue : [$filterValue]);
                    break;
                case 'address':
                    // address query goes here
                    $qb->andWhere($qb->expr()->orX(
                        $qb->expr()->like('obj.pc', ':address'),
                        $qb->expr()->like('obj.street', ':address'),
                        $qb->expr()->like('obj.town', ':address'),
                        $qb->expr()->like('obj.country', ':address'),
                        $qb->expr()->eq('obj.streetNumber', ':number'),
                        $qb->expr()->eq('obj.boxNumber', ':number')
                    ));

                    $qb->setParameter('address', '%'.$filterValue.'%');
                    $qb->setParameter('number', $filterValue);

                    break;

                default:
                    $this->setDefaults($qb, $filterName, $filterValue);

                    break;
            }
        }

        return $qb;
    }
}
