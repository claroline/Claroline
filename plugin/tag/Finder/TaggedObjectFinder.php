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
use Claroline\TagBundle\Entity\TaggedObject;
use Doctrine\ORM\QueryBuilder;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.api.finder.tagged_object")
 * @DI\Tag("claroline.finder")
 */
class TaggedObjectFinder extends AbstractFinder
{
    public function getClass()
    {
        return TaggedObject::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, array $options = ['count' => false, 'page' => 0, 'limit' => -1])
    {
        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'tag':
                    $qb->leftJoin('obj.tag', 't');
                    $qb->andWhere('t.uuid IN (:tagId)');
                    $qb->setParameter('tagId', $filterValue);
                    break;
                default:
                    $this->setDefaults($qb, $filterName, $filterValue);

                    break;
            }
        }

        return $qb;
    }
}
