<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\API\Finder\Planning;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Claroline\CoreBundle\Entity\Planning\PlannedObject;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

class PlannedObjectFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return PlannedObject::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, array $options = ['count' => false, 'page' => 0, 'limit' => -1])
    {
        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'planning':
                    $qb->innerJoin('Claroline\CoreBundle\Entity\Planning\Planning', 'p', Join::WITH, '1 = 1');
                    $qb->innerJoin('p.plannedObjects', 'po');
                    $qb->andWhere('obj.id = po.id');
                    $qb->andWhere('p.objectId = :objectId');
                    $qb->setParameter('objectId', $filterValue);
                    break;

                case 'types':
                    $types = is_array($filterValue) ? $filterValue : [$filterValue];
                    $qb->andWhere('obj.type IN (:eventTypes)');
                    $qb->setParameter('eventTypes', $types);
                    break;

                case 'inRange':
                    if (!empty($filterValue[0])) {
                        $qb->andWhere('obj.startDate >= :startRange OR obj.endDate >= :startRange2');
                        $qb->setParameter('startRange', $filterValue[0]);
                        $qb->setParameter('startRange2', $filterValue[0]);
                    }

                    if (!empty($filterValue[1])) {
                        $qb->andWhere('obj.startDate <= :endRange OR obj.endDate <= :endRange2');
                        $qb->setParameter('endRange', $filterValue[1]);
                        $qb->setParameter('endRange2', $filterValue[1]);
                    }

                    break;

                case 'afterToday':
                    if ($filterValue) {
                        $qb->andWhere("obj.startDate >= :{$filterName}");
                        $qb->setParameter($filterName, new \DateTime());
                    }
                    break;

                case 'location':
                    $qb->join('obj.location', 'l');
                    $qb->andWhere("l.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;

                case 'room':
                    $qb->join('obj.room', 'r');
                    $qb->andWhere("r.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;

                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
           }
        }

        $qb->andWhere($qb->expr()->gte('obj.endDate', 'obj.startDate'));

        return $qb;
    }

    public function getExtraFieldMapping()
    {
        return [
            'start' => 'startDate',
            'end' => 'endDate',
        ];
    }
}
