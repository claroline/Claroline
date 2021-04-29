<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\PlannedNotificationBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Claroline\PlannedNotificationBundle\Entity\Message;
use Doctrine\ORM\QueryBuilder;

class MessageFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return Message::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, array $options = ['count' => false, 'page' => 0, 'limit' => -1])
    {
        $qb->join('obj.workspace', 'w');
        $qb->andWhere('w.uuid = :workspaceUuid');
        $qb->setParameter('workspaceUuid', $searches['workspace']);

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'workspace':
                    break;
                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
            }
        }

        return $qb;
    }
}
