<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\API\Finder\Workspace;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Doctrine\ORM\QueryBuilder;

class PendingRegistrationFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return 'Claroline\CoreBundle\Entity\Workspace\WorkspaceRegistrationQueue';
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, array $options = ['count' => false, 'page' => 0, 'limit' => -1])
    {
        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'workspace':
                    $qb->leftJoin('obj.workspace', 'ws');
                    $qb->andWhere('ws.uuid = :wsUuid');
                    $qb->setParameter('wsUuid', $filterValue);
                    break;
            }
        }

        return $qb;
    }
}
