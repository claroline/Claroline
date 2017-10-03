<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\API\Finder;

use Claroline\CoreBundle\API\FinderInterface;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\QueryBuilder;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.api.finder.role")
 * @DI\Tag("claroline.finder")
 */
class RoleFinder implements FinderInterface
{
    public function getClass()
    {
        return 'Claroline\CoreBundle\Entity\Role';
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null)
    {
        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
              case 'user':
                  $qb->leftJoin('obj.users', 'ru');
                  $qb->andWhere("ru.username = :{$filterName}");
                  $qb->setParameter($filterName, $filterValue);
                  break;
              default:
                if ('true' === $filterValue || 'false' === $filterValue || true === $filterValue || false === $filterValue) {
                    $filterValue = is_string($filterValue) ? 'true' === $filterValue : $filterValue;
                    $qb->andWhere("obj.{$filterName} = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                } else {
                    $qb->andWhere("UPPER(obj.{$filterName}) LIKE :{$filterName}");
                    $qb->setParameter($filterName, '%'.strtoupper($filterValue).'%');
                }
            }
        }

        return $qb;
    }
}
