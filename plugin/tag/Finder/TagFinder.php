<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\TagBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Claroline\TagBundle\Entity\Tag;
use Doctrine\ORM\QueryBuilder;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.api.finder.tag")
 * @DI\Tag("claroline.finder")
 */
class TagFinder extends AbstractFinder
{
    public function getClass()
    {
        return Tag::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, array $options = ['count' => false, 'page' => 0, 'limit' => -1])
    {
        $objectJoin = false;
        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'objectClass':
                    if (!$objectJoin) {
                        $objectJoin = true;
                        $qb->join('obj.taggedObjects', 'to');
                    }

                    $qb->andWhere("to.objectClass = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);

                    break;

                case 'objectId':
                    if (!$objectJoin) {
                        $objectJoin = true;
                        $qb->join('obj.taggedObjects', 'to');
                    }

                    if (is_array($filterValue)) {
                        $qb->andWhere("to.objectId IN (:{$filterName})");
                        $qb->setParameter($filterName, $filterValue);
                    } else {
                        $qb->andWhere("to.objectId = :{$filterName}");
                        $qb->setParameter($filterName, $filterValue);
                    }

                    break;

                case 'user':
                    if ($filterValue) {
                        $qb->leftJoin('obj.user', 'u');
                        $qb->andWhere("(obj.user IS NULL OR u.uuid = :{$filterName})");
                    } else {
                        $qb->andWhere('obj.user IS NULL');
                    }
                    $qb->setParameter($filterName, $filterValue);

                    break;

                default:
                    if (is_string($filterValue)) {
                        $qb->andWhere("UPPER(obj.{$filterName}) LIKE :{$filterName}");
                        $qb->setParameter($filterName, '%'.strtoupper($filterValue).'%');
                    } else {
                        $qb->andWhere("obj.{$filterName} = :{$filterName}");
                        $qb->setParameter($filterName, $filterValue);
                    }

                    break;
            }
        }

        return $qb;
    }
}
