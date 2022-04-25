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

class TagFinder extends AbstractFinder
{
    public static function getClass(): string
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

                default:
                    $this->setDefaults($qb, $filterName, $filterValue);

                    break;
            }
        }

        return $qb;
    }
}
