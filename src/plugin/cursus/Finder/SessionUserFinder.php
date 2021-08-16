<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Claroline\CursusBundle\Entity\Registration\SessionUser;
use Doctrine\ORM\QueryBuilder;

class SessionUserFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return SessionUser::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, array $options = ['count' => false, 'page' => 0, 'limit' => -1])
    {
        $sessionJoin = false;

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'status':
                    $status = [
                        'managed' => [
                            'validated' => '1',
                            'managed' => '1',
                            'refused' => '0'
                        ],
                        'validated' => [
                            'validated' => '1',
                            'managed' => '0',
                            'refused' => '0'
                        ],
                        'refused' => [
                            'validated' => '0',
                            'managed' => '0',
                            'refused' => '1'
                        ],
                        'pending' => [
                            'validated' => '0',
                            'managed' => '0',
                            'refused' => '0'
                        ]
                    ];
                    if (isset($status[$filterValue])) {
                        $qb->andWhere('(obj.validated = :validated)');
                        $qb->andWhere('(obj.managed = :managed)');
                        $qb->andWhere('(obj.refused = :refused)');
                        $qb->setParameters($status[$filterValue]);
                    }
                    break;

                case 'year':
                    break;

                case 'organization':
                    $qb->join('obj.session', 's');
                    $qb->join('s.course', 'c');
                    $qb->join('c.organizations', 'o');
                    $qb->andWhere('o IN (:organization)');
                    $qb->setParameter($filterName, $filterValue);
                    break;

                case 'course':
                    if (!$sessionJoin) {
                        $qb->join('obj.session', 's');
                        $sessionJoin = true;
                    }
                    $qb->join('s.course', 'c');
                    $qb->andWhere("c.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;

                case 'session':
                    if (!$sessionJoin) {
                        $qb->join('obj.session', 's');
                        $sessionJoin = true;
                    }
                    $qb->andWhere("s.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;

                case 'user':
                    $qb->join('obj.user', 'u');
                    $qb->andWhere("u.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;

                case 'pending':
                    if ($filterValue) {
                        $qb->andWhere('(obj.confirmed = 0 OR obj.validated = 0)');
                    } else {
                        $qb->andWhere('(obj.confirmed = 1 AND obj.validated = 1)');
                    }
                    break;

                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
            }
        }

        return $qb;
    }
}
