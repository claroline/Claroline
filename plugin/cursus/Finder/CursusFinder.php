<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Doctrine\ORM\QueryBuilder;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.api.finder.cursus")
 * @DI\Tag("claroline.finder")
 */
class CursusFinder extends AbstractFinder
{
    public function getClass()
    {
        return 'Claroline\CursusBundle\Entity\Cursus';
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null)
    {
        $qb->join('obj.organizations', 'o');
        $qb->andWhere('o.uuid IN (:organizations)');
        $qb->setParameter('organizations', $searches['organizations']);

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'organizations':
                    break;
                case 'parent':
                    if (empty($filterValue)) {
                        $qb->andWhere('obj.parent IS NULL');
                    } else {
                        $qb->leftJoin('obj.parent', 'p');
                        $qb->andWhere('p.uuid IN (:parentIds)');
                        $qb->setParameter('parentIds', is_array($filterValue) ? $filterValue : [$filterValue]);
                    }
                    break;
                default:
                    if (is_bool($filterValue)) {
                        $qb->andWhere("obj.{$filterName} = :{$filterName}");
                        $qb->setParameter($filterName, $filterValue);
                    } else {
                        $qb->andWhere("UPPER(obj.{$filterName}) LIKE :{$filterName}");
                        $qb->setParameter($filterName, '%'.strtoupper($filterValue).'%');
                    }
            }
        }

        return $qb;
    }
}
