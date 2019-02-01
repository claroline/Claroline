<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\OpenBadgeBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Claroline\OpenBadgeBundle\Entity\Evidence;
use Doctrine\ORM\QueryBuilder;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.api.finder.openbadge.evidence")
 * @DI\Tag("claroline.finder")
 */
class EvidenceFinder extends AbstractFinder
{
    public function getClass()
    {
        return Evidence::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, array $options = [])
    {
        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
              case 'assertion':
                  $qb->join('obj.assertion', 'a');
                  $qb->andWhere('a.uuid like :assertion');
                  $qb->setParameter('assertion', $filterValue);
                  break;
              default:
                  $this->setDefaults($qb, $filterName, $filterValue);
            }
        }

        return $qb;
    }
}
