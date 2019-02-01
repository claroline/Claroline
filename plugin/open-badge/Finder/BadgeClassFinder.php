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
use Claroline\OpenBadgeBundle\Entity\BadgeClass;
use Doctrine\ORM\QueryBuilder;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.api.finder.openbadge.badgeclass")
 * @DI\Tag("claroline.finder")
 */
class BadgeClassFinder extends AbstractFinder
{
    public function getClass()
    {
        return BadgeClass::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, array $options = [])
    {
        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
              case 'recipient':
                  $qb->join('obj.assertions', 'a');
                  $qb->join('a.recipient', 'r');
                  $qb->andWhere('r.uuid like :uuid');
                  $qb->setParameter('uuid', $filterValue);
                  break;
              case 'workspace':
                  $qb->join('obj.workspace', 'w');
                  $qb->andWhere('w.uuid like :workspace');
                  $qb->setParameter('workspace', $filterValue);
                  break;
              case 'meta.enabled':
                  $qb->andWhere('obj.enabled = :enabled');
                  $qb->setParameter('enabled', $filterValue);
                  break;
              default:
                $this->setDefaults($qb, $filterName, $filterValue);
            }
        }

        return $qb;
    }
}
