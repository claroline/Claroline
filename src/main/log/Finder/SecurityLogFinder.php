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
use Claroline\LogBundle\Entity\SecurityLog;
use Doctrine\ORM\QueryBuilder;

class SecurityLogFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return SecurityLog::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, array $options = ['count' => false, 'page' => 0, 'limit' => -1])
    {
        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'doer':
                    $qb->leftJoin('obj.doer', 'd');
                    $qb->andWhere('d.uuid = :id');
                    $qb->setParameter('id', $filterValue);
                    break;

                case 'target':
                    $qb->leftJoin('obj.target', 't');
                    $qb->andWhere('t.uuid = :id');
                    $qb->setParameter('id', $filterValue);
                    break;

                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
            }
        }

        return $qb;
    }
}
