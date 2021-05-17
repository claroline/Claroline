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

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Claroline\CoreBundle\Entity\Import\File;
use Doctrine\ORM\QueryBuilder;

class TransferFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return File::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, array $options = ['count' => false, 'page' => 0, 'limit' => -1])
    {
        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'workspace':
                    $qb->leftJoin('obj.workspace', 'w');
                    $qb->andWhere('w.uuid = :workspaceId');
                    $qb->setParameter('workspaceId', $filterValue);
                    break;

                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
            }
        }

        return $qb;
    }
}
