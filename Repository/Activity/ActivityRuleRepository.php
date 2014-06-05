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

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Doctrine\ORM\EntityRepository;

class ActivityRuleRepository extends EntityRepository
{
    public function findActivityRuleByActionAndResource(
        $action,
        $resourceNodeId,
        $executeQuery = true
    )
    {
        $dql = '
            SELECT ar
            FROM Claroline\CoreBundle\Entity\Activity\ActivityRule ar
            JOIN ar.resource res
            WHERE ar.action = :action
            AND res.id = :resourceNodeId
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('action', $action);
        $query->setParameter('resourceNodeId', $resourceNodeId);

        return $executeQuery ? $query->getResult(): $query;
    }
}
