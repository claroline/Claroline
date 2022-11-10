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

use Claroline\AgendaBundle\Entity\EventInvitation;
use Claroline\AppBundle\API\Finder\AbstractFinder;
use Doctrine\ORM\QueryBuilder;

class EventInvitationFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return EventInvitation::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null): QueryBuilder
    {
        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'user':
                    $qb->leftJoin('obj.user', 'u');
                    $qb->andWhere('u.uuid = :userId');
                    $qb->setParameter('userId', $filterValue);
                    break;
                case 'event':
                    $qb->leftJoin('obj.event', 'e');
                    $qb->andWhere('e.uuid = :eventId');
                    $qb->setParameter('eventId', $filterValue);
                    break;
                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
            }
        }

        return $qb;
    }
}
