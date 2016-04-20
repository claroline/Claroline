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

use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Doctrine\ORM\EntityRepository;

class ActivityRuleActionRepository extends EntityRepository
{
    public function findRuleActionsByResourceType(
        ResourceType $resourceType = null,
        $executeQuery = true
    ) {
        $dql = '
            SELECT ara
            FROM Claroline\CoreBundle\Entity\Activity\ActivityRuleAction ara
            WHERE ara.resourceType = :resourceType
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('resourceType', $resourceType);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findRuleActionsWithNoResourceType($executeQuery = true)
    {
        $dql = '
            SELECT ara
            FROM Claroline\CoreBundle\Entity\Activity\ActivityRuleAction ara
            WHERE ara.resourceType IS NULL
        ';

        $query = $this->_em->createQuery($dql);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findAllDistinctActivityRuleActions($executeQuery = true)
    {
        $dql = '
            SELECT DISTINCT ara.action
            FROM Claroline\CoreBundle\Entity\Activity\ActivityRuleAction ara
            ORDER BY ara.action ASC
        ';

        $query = $this->_em->createQuery($dql);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findRuleActionByActionAndResourceType(
        $action,
        ResourceType $resourceType,
        $executeQuery = true
    ) {
        $dql = '
            SELECT ara
            FROM Claroline\CoreBundle\Entity\Activity\ActivityRuleAction ara
            WHERE ara.action = :action
            AND ara.resourceType = :resourceType
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('action', $action);
        $query->setParameter('resourceType', $resourceType);

        return $executeQuery ? $query->getOneOrNullResult() : $query;
    }

    public function findRuleActionByActionWithNoResourceType(
        $action,
        $executeQuery = true
    ) {
        $dql = '
            SELECT ara
            FROM Claroline\CoreBundle\Entity\Activity\ActivityRuleAction ara
            WHERE ara.action = :action
            AND ara.resourceType IS NULL
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('action', $action);

        return $executeQuery ? $query->getOneOrNullResult() : $query;
    }
}
