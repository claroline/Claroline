<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\API\Finder\Log;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Claroline\CoreBundle\Entity\Log\SecurityLog;
use Doctrine\ORM\QueryBuilder;

class SecurityLogFinder extends AbstractFinder
{
    public function getClass()
    {
        return SecurityLog::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, array $options = ['count' => false, 'page' => 0, 'limit' => -1])
    {
        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'user':
                    $qb->leftJoin('obj.doer', 'd');
                    $qb->andWhere('d.uuid = :id');
                    $qb->setParameter('id', $filterValue);
                    break;
                case 'email':
                    $qb->orWhere('obj.details LIKE :email');
                    $qb->setParameter('email', '%'.$filterValue.'%');
                    break;

                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
            }
        }

        $qb->orderBy('obj.id', 'desc');

        return $qb;
    }
}
