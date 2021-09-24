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
        $qb->join('obj.user', 'u');
        $qb->join('obj.session', 's');
        $qb->join('s.course', 'c');

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'status':
                    $qb->andWhere("(obj.status = :{$filterName})");
                    $qb->setParameter($filterName, $filterValue);
                    break;

                case 'ignored_status':
                    $qb->andWhere("obj.status != :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;

                case 'organization':
                    $qb->leftJoin('u.userOrganizationReferences', 'oref');
                    $qb->andWhere("oref.organization = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;

                case 'course':
                    $qb->andWhere("c.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;

                case 'session':
                    $qb->andWhere("s.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;

                case 'user':
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
