<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AuthenticationBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Claroline\AuthenticationBundle\Entity\IpUser;
use Doctrine\ORM\QueryBuilder;

class IpUserFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return IpUser::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null): QueryBuilder
    {
        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'ip': // not setDefaults because partial IP searches are caught as number
                    $qb->andWhere("obj.{$filterName} LIKE :{$filterName}");
                    $qb->setParameter($filterName, '%'.$filterValue.'%');
                    break;

                case 'user':
                    $qb->join('obj.user', 'u');
                    $qb->andWhere('u.uuid = :userId');
                    $qb->setParameter('userId', $filterValue);
                    break;

                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
            }
        }

        return $qb;
    }
}
