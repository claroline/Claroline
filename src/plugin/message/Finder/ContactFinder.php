<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\MessageBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Claroline\MessageBundle\Entity\Contact\Contact;
use Doctrine\ORM\QueryBuilder;

class ContactFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return Contact::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null): QueryBuilder
    {
        $qb->join('obj.user', 'u');
        $qb->join('obj.contact', 'c');

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'user':
                    $qb->andWhere('u.id = :userId');
                    $qb->setParameter('userId', $searches['user']);
                    break;
                case 'username':
                case 'firstName':
                case 'lastName':
                case 'phone':
                case 'email':
                    $qb->andWhere("UPPER(c.{$filterName}) LIKE :{$filterName}");
                    $qb->setParameter($filterName, '%'.strtoupper($filterValue).'%');
                    break;
                case 'group':
                    $qb->join('c.groups', 'g');
                    $qb->andWhere('UPPER(g.name) LIKE :group');
                    $qb->setParameter('group', '%'.strtoupper($filterValue).'%');
                    break;
            }
        }
        if (!is_null($sortBy) && isset($sortBy['property']) && isset($sortBy['direction'])) {
            $sortByProperty = $sortBy['property'];
            $sortByDirection = 1 === $sortBy['direction'] ? 'ASC' : 'DESC';

            switch ($sortByProperty) {
                case 'username':
                case 'firstName':
                case 'lastName':
                case 'phone':
                case 'email':
                    $qb->orderBy("c.{$sortByProperty}", $sortByDirection);
                    break;
            }
        }

        return $qb;
    }
}
