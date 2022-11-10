<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icap\NotificationBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Doctrine\ORM\QueryBuilder;
use Icap\NotificationBundle\Entity\NotificationViewer;

class NotificationViewerFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return NotificationViewer::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null): QueryBuilder
    {
        $qb->join('obj.notification', 'notification');

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'read':
                    $qb->andWhere('obj.status = :filter');
                    $qb->setParameter('filter', $filterValue);
                    break;
              case 'user':
                $qb->andWhere('obj.viewerId = :viewerId');
                $qb->setParameter('viewerId', $filterValue);
                break;
              case 'types':
                foreach ($filterValue as $name) {
                    $qb->andWhere(
                      $qb->expr()
                        ->notLike(
                          'notification.actionKey',
                          $qb->expr()->literal('%'.$name.'%')
                      )
                    );
                    break;
                }
                break;
          }
        }

        if (!is_null($sortBy) && isset($sortBy['property']) && isset($sortBy['direction'])) {
            $sortByProperty = $sortBy['property'];
            $sortByDirection = 1 === $sortBy['direction'] ? 'ASC' : 'DESC';

            switch ($sortByProperty) {
                case 'notification.meta.created':
                    $qb->orderBy('notification.creationDate', $sortByDirection);
                    break;
            }
        }

        return $qb;
    }
}
