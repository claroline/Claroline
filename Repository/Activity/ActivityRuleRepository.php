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
        ResourceNode $resourceNode = null,
        $executeQuery = true
    )
    {
        $dql = '
            SELECT r
            FROM Claroline\CoreBundle\Entity\Activity\ActivityRule r
            WHERE r.action = :action
            AND r.resource = :resourceNode
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('action', $action);
        $query->setParameter('resourceNode', $resourceNode);

        return $executeQuery ? $query->getResult(): $query;
    }
}
