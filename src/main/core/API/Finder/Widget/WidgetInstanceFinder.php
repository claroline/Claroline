<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\API\Finder\Widget;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Doctrine\ORM\QueryBuilder;

/**
 * @deprecated this should not exist (this is only for high level search)
 */
class WidgetInstanceFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return WidgetInstance::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null): QueryBuilder
    {
        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'container':
                  $qb->join('obj.container', 't');
                  $qb->andWhere('t.uuid = :container');
                  $qb->setParameter('container', $filterValue);
                  break;
                case 'homeTab':
                  $qb->join('obj.container', 'c');
                  $qb->join('c.homeTab', 't');
                  $qb->andWhere('t.uuid = :homeTab');
                  $qb->setParameter('homeTab', $filterValue);
                  break;
            }
        }

        return $qb;
    }
}
