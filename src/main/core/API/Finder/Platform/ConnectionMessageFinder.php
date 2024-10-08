<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\API\Finder\Platform;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Claroline\CoreBundle\Entity\ConnectionMessage\ConnectionMessage;
use Doctrine\ORM\QueryBuilder;

/**
 * @deprecated
 */
class ConnectionMessageFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return ConnectionMessage::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, ?int $page = 0, ?int $limit = -1): QueryBuilder
    {
        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'restrictions.hidden':
                    $qb->andWhere("obj.hidden = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
            }
        }

        return $qb;
    }
}
