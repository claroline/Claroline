<?php
/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Doctrine\ORM\EntityRepository;

class ToolRightsRepository extends EntityRepository
{
    public function findRightsByOrderedTool(
        OrderedTool $orderedTool,
        $executeQuery = true
    ) {
        $dql = '
            SELECT tr
            FROM Claroline\CoreBundle\Entity\Tool\ToolRights tr
            WHERE tr.orderedTool = :orderedTool
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('orderedTool', $orderedTool);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findRightsByRoleAndOrderedTool(
        Role $role,
        OrderedTool $orderedTool,
        $executeQuery = true
    ) {
        $dql = '
            SELECT tr
            FROM Claroline\CoreBundle\Entity\Tool\ToolRights tr
            WHERE tr.role = :role
            AND tr.orderedTool = :orderedTool
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('role', $role);
        $query->setParameter('orderedTool', $orderedTool);

        return $executeQuery ? $query->getOneOrNullResult() : $query;
    }

    public function findRightsForOrderedTools(
        array $orderedTools,
        $executeQuery = true
    ) {
        if (count($orderedTools) === 0) {
            return array();
        } else {
            $dql = '
                SELECT tr
                FROM Claroline\CoreBundle\Entity\Tool\ToolRights tr
                WHERE tr.orderedTool IN (:orderedTools)
            ';
            $query = $this->_em->createQuery($dql);
            $query->setParameter('orderedTools', $orderedTools);

            return $executeQuery ? $query->getResult() : $query;
        }
    }
}
