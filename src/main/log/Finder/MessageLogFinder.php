<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\LogBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Claroline\LogBundle\Entity\MessageLog;
use Doctrine\ORM\QueryBuilder;

class MessageLogFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return MessageLog::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null): QueryBuilder
    {
        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'sender':
                    $qb->leftJoin('obj.sender', 's');
                    $qb->andWhere('s.uuid IN (:sender)');
                    $qb->setParameter('sender', is_array($filterValue) ? $filterValue : [$filterValue]);
                    break;

                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
            }
        }

        return $qb;
    }
}
