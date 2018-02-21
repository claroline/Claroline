<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\DropZoneBundle\Finder;

use Claroline\AppBundle\API\FinderInterface;
use Doctrine\ORM\QueryBuilder;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.api.finder.dropzone.tool")
 * @DI\Tag("claroline.finder")
 */
class DropzoneToolFinder implements FinderInterface
{
    public function getClass()
    {
        return 'Claroline\DropZoneBundle\Entity\DropzoneTool';
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null)
    {
        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                default:
                    if (is_bool($filterValue)) {
                        $qb->andWhere("obj.{$filterName} = :{$filterName}");
                        $qb->setParameter($filterName, $filterValue);
                    } else {
                        if (is_int($filterValue)) {
                            $qb->andWhere("obj.{$filterName} = :{$filterName}");
                            $qb->setParameter($filterName, $filterValue);
                        } else {
                            $qb->andWhere("UPPER(obj.{$filterName}) LIKE :{$filterName}");
                            $qb->setParameter($filterName, '%'.strtoupper($filterValue).'%');
                        }
                    }
            }
        }

        return $qb;
    }
}
