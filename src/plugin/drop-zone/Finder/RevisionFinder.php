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

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Claroline\DropZoneBundle\Entity\Revision;
use Doctrine\ORM\QueryBuilder;

class RevisionFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return Revision::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null): QueryBuilder
    {
        $qb->join('obj.drop', 'drop');
        $qb->join('drop.dropzone', 'd');

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'dropzone':
                    $qb->andWhere("d.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'drop':
                    $qb->andWhere("drop.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'creator':
                    $qb->join('obj.creator', 'u');
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
                    $this->setDefaults($qb, $filterName, $filterValue);
            }
        }

        return $qb;
    }
}
