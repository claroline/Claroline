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
use Claroline\LogBundle\Entity\FunctionalLog;
use Doctrine\ORM\QueryBuilder;

class FunctionalLogFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return FunctionalLog::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null): QueryBuilder
    {
        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'doer':
                    $qb->leftJoin('obj.doer', 'u');
                    $qb->andWhere('u.uuid = :doer');
                    $qb->setParameter('doer', $filterValue);
                    break;

                case 'workspace':
                    $qb->leftJoin('obj.workspace', 'w');
                    $qb->andWhere('w.uuid = :workspace');
                    $qb->setParameter('workspace', $filterValue);
                    break;

                case 'resource':
                    $qb->leftJoin('obj.resource', 'n');
                    $qb->andWhere('n.uuid = :resource');
                    $qb->setParameter('resource', $filterValue);
                    break;

                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
            }
        }

        return $qb;
    }
}
