<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\API\Finder\Resource;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Claroline\CoreBundle\Entity\Resource\ResourceRights;
use Doctrine\ORM\QueryBuilder;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.api.finder.resource_rights")
 * @DI\Tag("claroline.finder")
 */
class ResourceRightsFinder extends AbstractFinder
{
    public function getClass()
    {
        return ResourceRights::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, array $options = ['count' => false, 'page' => 0, 'limit' => -1])
    {
        $qb->join('obj.resourceNode', 'node');
        $qb->join('node.resourceType', 'ort');

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
              case 'resourceType':
                  if (is_array($filterValue)) {
                      $qb->andWhere('ort.name IN (:resourceType)');
                  } else {
                      $qb->andWhere('ort.name LIKE :resourceType');
                  }
                  $qb->setParameter('resourceType', $filterValue);
                  break;
                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
                    break;
            }
        }

        return $qb;
    }

    public function getFilters()
    {
        return [
            '$defaults' => [],
        ];
    }
}
