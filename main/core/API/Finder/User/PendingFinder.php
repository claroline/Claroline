<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\API\Finder\User;

use Claroline\AppBundle\API\FinderInterface;
use Doctrine\ORM\QueryBuilder;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.api.finder.pending")
 * @DI\Tag("claroline.finder")
 */
class PendingFinder implements FinderInterface
{
    public function getClass()
    {
        return 'Claroline\CoreBundle\Entity\Workspace\WorkspaceRegistrationQueue';
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null)
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
