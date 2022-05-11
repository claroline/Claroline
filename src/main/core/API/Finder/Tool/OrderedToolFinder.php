<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\API\Finder\Tool;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Doctrine\ORM\QueryBuilder;

class OrderedToolFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return OrderedTool::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, array $options = ['count' => false, 'page' => 0, 'limit' => -1])
    {
        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'workspace':
                    $qb->leftJoin('obj.workspace', 'ws');
                    $qb->andWhere($qb->expr()->orX(
                        $qb->expr()->eq('ws.uuid', ':workspace')
                    ));
                    $qb->setParameter('workspace', $filterValue);
                    break;
                case 'tool':
                    $qb->leftJoin('obj.tool', 'tool');
                    $qb->andWhere('tool.name = :tool');
                    $qb->setParameter('tool', $filterValue);
            }
        }

        return $qb;
    }
}
