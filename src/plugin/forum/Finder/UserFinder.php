<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ForumBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Claroline\ForumBundle\Entity\Validation\User;
use Doctrine\ORM\QueryBuilder;

class UserFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return User::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null): QueryBuilder
    {
        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'forum':
                    $qb->leftJoin('obj.forum', 'forum');
                    $qb->andWhere("forum.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
            }
        }

        return $qb;
    }
}
