<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\API\Finder\User;

use Claroline\AppBundle\API\FinderInterface;
use Doctrine\ORM\QueryBuilder;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.api.finder.theme")
 * @DI\Tag("claroline.finder")
 */
class LocationFinder implements FinderInterface
{
    public function getClass()
    {
        return 'Claroline\CoreBundle\Entity\Organization\Location';
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null)
    {
        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
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
                $qb->andWhere("UPPER(obj.{$filterName}) LIKE :{$filterName}");
                $qb->setParameter($filterName, '%'.strtoupper($filterValue).'%');

                break;
            }
        }

        return $qb;
    }
}
