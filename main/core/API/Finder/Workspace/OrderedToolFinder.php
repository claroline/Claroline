<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\API\Finder\Workspace;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Doctrine\ORM\QueryBuilder;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.api.finder.ordered_tool")
 * @DI\Tag("claroline.finder")
 */
class OrderedToolFinder extends AbstractFinder
{
    public function getClass()
    {
        return 'Claroline\CoreBundle\Entity\Tool\OrderedTool';
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null)
    {
        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'workspace':
                    $qb->leftJoin('obj.workspace', 'ws');
                    $qb->andWhere($qb->expr()->orX(
                        $qb->expr()->eq('ws.id', ':workspace'),
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
