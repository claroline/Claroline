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

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, ?int $page = 0, ?int $limit = -1): QueryBuilder
    {
        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'context':
                    $qb->andWhere('obj.contextName = :contextName');
                    $qb->setParameter('contextName', $filterValue);
                    break;
                case 'contextId':
                    $qb->andWhere('obj.contextId = :contextId');
                    $qb->setParameter('contextId', $filterValue);
                    break;
                case 'tool':
                    $qb->leftJoin('obj.tool', 'tool');
                    $qb->andWhere('tool.name = :tool');
                    $qb->setParameter('tool', $filterValue);
                    break;
                case 'roles':
                    $qb->join('obj.rights', 'r');
                    $qb->join('r.role', 'rr');
                    $qb->andWhere('BIT_AND(r.mask, 1) = 1');
                    $qb->andWhere("rr.name IN (:{$filterName})");
                    $qb->setParameter($filterName, is_array($filterValue) ? $filterValue : [$filterValue]);
                    break;
                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
                    break;
            }
        }

        return $qb;
    }
}
