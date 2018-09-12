<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AgendaBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Doctrine\ORM\QueryBuilder;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.api.finder.agenda")
 * @DI\Tag("claroline.finder")
 */
class EventFinder extends AbstractFinder
{
    public function getClass()
    {
        return 'Claroline\AgendaBundle\Entity\Event';
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null)
    {
        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
              case 'workspaces':
                $qb->leftJoin('obj.workspace', 'w');

                //if $filterValue = 0, it means desktop
                if (in_array(0, $filterValue)) {
                    $qb->andWhere($qb->expr()->orX(
                        $qb->expr()->in('w.uuid', ':'.$filterName),
                        $qb->expr()->isNull('w')
                    ));

                    $qb->setParameter($filterName, $filterValue);
                } else {
                    $qb->andWhere('w.uuid IN (:'.$filterName.')');
                    $qb->setParameter($filterName, $filterValue);
                }
                break;
              case 'types':
                if ($filterValue === ['task']) {
                    $qb->andWhere('obj.isTask = true');
                } elseif ($filterValue === ['event']) {
                    $qb->andWhere('obj.isTask = false');
                }
                break;
              case 'createdBefore':
                $qb->andWhere("obj.start <= :{$filterName}");
                $qb->setParameter($filterName, $filterValue);
                break;
              case 'createdAfter':
                $qb->andWhere("obj.start >= :{$filterName}");
                $qb->setParameter($filterName, $filterValue);
                break;
              case 'endBefore':
                $qb->andWhere("obj.end <= :{$filterName}");
                $qb->setParameter($filterName, $filterValue);
                break;
              case 'endAfter':
                $qb->andWhere("obj.end >= :{$filterName}");
                $qb->setParameter($filterName, $filterValue);
                break;
              case 'endBeforeNow':
                $qb->andWhere("obj.end <= :{$filterName}");
                $qb->setParameter($filterName, new \DateTime());
                break;
              case 'endAfterNow':
                $qb->andWhere("obj.end >= :{$filterName}");
                $qb->setParameter($filterName, new \DateTime());
                break;
              default:
                $this->setDefaults($qb, $filterName, $filterValue);
             }
        }

        return $qb;
    }
}
