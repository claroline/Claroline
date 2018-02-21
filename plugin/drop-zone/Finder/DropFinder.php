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
 * @DI\Service("claroline.api.finder.dropzone.drop")
 * @DI\Tag("claroline.finder")
 */
class DropFinder implements FinderInterface
{
    public function getClass()
    {
        return 'Claroline\DropZoneBundle\Entity\Drop';
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null)
    {
        $qb->join('obj.dropzone', 'd');
        $qb->andWhere('d.uuid = :dropzoneUuid');
        $qb->setParameter('dropzoneUuid', $searches['dropzone']);

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'dropzone':
                    break;
                case 'user':
                    $qb->join('obj.user', 'u');
                    $qb->andWhere("
                        UPPER(u.firstName) LIKE :name
                        OR UPPER(u.lastName) LIKE :name
                        OR UPPER(u.username) LIKE :name
                        OR CONCAT(UPPER(u.firstName), CONCAT(' ', UPPER(u.lastName))) LIKE :name
                        OR CONCAT(UPPER(u.lastName), CONCAT(' ', UPPER(u.firstName))) LIKE :name
                    ");
                    $qb->setParameter('name', '%'.strtoupper($filterValue).'%');
                    break;
                default:
                    if (is_bool($filterValue)) {
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
