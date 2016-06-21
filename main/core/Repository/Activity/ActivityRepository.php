<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Repository\Activity;

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\ORM\EntityRepository;

class ActivityRepository extends EntityRepository
{
    public function findActivityByWorkspace(
        Workspace $workspace,
        $executeQuery = true
    ) {
        $dql = '
            SELECT a
            FROM Claroline\CoreBundle\Entity\Resource\Activity a
            JOIN a.resourceNode r
            WHERE r.workspace = (:workspace)
            ORDER BY a.title ASC
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findActivitiesByResourceNodeIds(
        array $resourceNodeIds,
        $executeQuery = true
    ) {
        $dql = '
            SELECT a
            FROM Claroline\CoreBundle\Entity\Resource\Activity a
            JOIN a.resourceNode r
            WHERE r.id IN (:resourceNodeIds)
            ORDER BY a.title ASC
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('resourceNodeIds', $resourceNodeIds);

        return $executeQuery ? $query->getResult() : $query;
    }
}
