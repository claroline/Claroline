<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Finder\Registration;

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
        $userJoin = false;
        $sessionJoin = false;

        if (!array_key_exists('userDisabled', $searches) && !array_key_exists('user', $searches)) {
            // don't show registrations of disabled/deleted users
            $qb->join('obj.user', 'u');
            $userJoin = true;

            $qb->andWhere('u.isEnabled = TRUE');
            $qb->andWhere('u.isRemoved = FALSE');
        }

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
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
                    if (!$userJoin) {
                        $qb->join('obj.user', 'u');
                        $userJoin = true;
                    }

                    $qb->andWhere("u.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;

                case 'userDisabled':
                    if (!$userJoin) {
                        $qb->join('obj.user', 'u');
                        $userJoin = true;
                    }
                    $qb->andWhere('u.isEnabled = :isEnabled');
                    $qb->andWhere('u.isRemoved = FALSE');
                    $qb->setParameter('isEnabled', !$filterValue);
                    break;

                case 'organizations':
                    if (!$userJoin) {
                        $qb->join('obj.user', 'u');
                        $userJoin = true;
                    }

                    $qb->join('u.organizations', 'o');
                    $qb->andWhere("o.uuid IN (:{$filterName})");
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
