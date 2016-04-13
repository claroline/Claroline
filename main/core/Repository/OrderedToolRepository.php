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

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;

class OrderedToolRepository extends EntityRepository
{
    /**
     * Returns the workspace ordered tools accessible to some given roles.
     *
     * @param Workspace $workspace
     * @param array     $roles
     *
     * @return array[OrderedTool]
     */
    public function findByWorkspaceAndRoles(
        Workspace $workspace,
        array $roles,
        $type = 0
    ) {
        if (count($roles) === 0) {
            return array();
        } else {
            $dql = '
                SELECT ot
                FROM Claroline\CoreBundle\Entity\Tool\OrderedTool ot
                JOIN ot.rights r
                JOIN r.role rr
                WHERE ot.workspace = :workspace
                AND ot.type = :type
                AND rr.name IN (:roleNames)
                AND BIT_AND(r.mask, 1) = 1
                ORDER BY ot.order
            ';
            $query = $this->_em->createQuery($dql);
            $query->setParameter('workspace', $workspace);
            $query->setParameter('roleNames', $roles);
            $query->setParameter('type', $type);

            return $query->getResult();
        }
    }

    public function incWorkspaceOrderedToolOrderForRange(
        Workspace $workspace,
        $fromOrder,
        $toOrder,
        $type = 0,
        $executeQuery = true
    ) {
        $dql = '
            UPDATE Claroline\CoreBundle\Entity\Tool\OrderedTool ot
            SET ot.order = ot.order + 1
            WHERE ot.workspace = :workspace
            AND ot.type = :type
            AND ot.order >= :fromOrder
            AND ot.order < :toOrder
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);
        $query->setParameter('fromOrder', $fromOrder);
        $query->setParameter('toOrder', $toOrder);
        $query->setParameter('type', $type);

        return $executeQuery ? $query->execute() : $query;
    }

    public function decWorkspaceOrderedToolOrderForRange(
        Workspace $workspace,
        $fromOrder,
        $toOrder,
        $type = 0,
        $executeQuery = true
    ) {
        $dql = '
            UPDATE Claroline\CoreBundle\Entity\Tool\OrderedTool ot
            SET ot.order = ot.order - 1
            WHERE ot.workspace = :workspace
            AND ot.type = :type
            AND ot.order > :fromOrder
            AND ot.order <= :toOrder
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);
        $query->setParameter('fromOrder', $fromOrder);
        $query->setParameter('toOrder', $toOrder);
        $query->setParameter('type', $type);

        return $executeQuery ? $query->execute() : $query;
    }

    public function findPersonalDisplayableByWorkspaceAndRoles(
        Workspace $workspace,
        array $roles,
        $type = 0
    ) {
        $dql = 'SELECT ot
            FROM Claroline\CoreBundle\Entity\Tool\OrderedTool ot
            JOIN ot.tool t
            JOIN ot.rights r
            JOIN r.role rr
            JOIN t.pwsToolConfig ptc
            WHERE ot.workspace = :workspace
            AND ot.type = :type
            AND rr.name IN (:roleNames)
            AND BIT_AND(r.mask, 1) = 1
            AND BIT_AND(ptc.mask, 1) = 1
            ORDER BY ot.order
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('roleNames', $roles);
        $query->setParameter('workspace', $workspace);
        $query->setParameter('type', $type);

        return $query->getResult();
    }

    public function findPersonalDisplayable(Workspace $workspace, $type = 0)
    {
        $dql = 'SELECT ot
            FROM Claroline\CoreBundle\Entity\Tool\OrderedTool ot
            JOIN ot.tool t
            JOIN t.pwsToolConfig ptc
            JOIN ot.workspace workspace
            WHERE BIT_AND(ptc.mask, 1) = 1
            AND workspace.id = :workspaceId
            AND ot.type = :type
            ORDER BY ot.order
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspaceId', $workspace->getId());
        $query->setParameter('type', $type);

        return $query->getResult();
    }

    public function findDisplayableDesktopOrderedToolsByUser(
        User $user,
        $type = 0,
        $executeQuery = true
    ) {
        $dql = '
            SELECT ot
            FROM Claroline\CoreBundle\Entity\Tool\OrderedTool ot
            JOIN ot.tool t
            WHERE ot.workspace IS NULL
            AND ot.type = :type
            AND ot.user = :user
            AND t.isDisplayableInDesktop = true
            ORDER BY ot.order
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);
        $query->setParameter('type', $type);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findConfigurableDesktopOrderedToolsByUser(
        User $user,
        array $excludedToolNames,
        $type = 0,
        $executeQuery = true
    ) {
        $dql = '
            SELECT ot
            FROM Claroline\CoreBundle\Entity\Tool\OrderedTool ot
            JOIN ot.tool t
            WHERE ot.workspace IS NULL
            AND ot.type = :type
            AND ot.user = :user
            AND t.name NOT IN (:excludedToolNames)
            AND t.isDisplayableInDesktop = true
            ORDER BY ot.order
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);
        $query->setParameter('excludedToolNames', $excludedToolNames);
        $query->setParameter('type', $type);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findDisplayableDesktopOrderedToolsByTypeForAdmin(
        $type = 0,
        $executeQuery = true
    ) {
        $dql = '
            SELECT ot
            FROM Claroline\CoreBundle\Entity\Tool\OrderedTool ot
            JOIN ot.tool t
            WHERE ot.workspace IS NULL
            AND ot.user IS NULL
            AND ot.type = :type
            AND t.isDisplayableInDesktop = true
            ORDER BY ot.order
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('type', $type);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findConfigurableDesktopOrderedToolsByTypeForAdmin(
        array $excludedToolNames,
        $type = 0,
        $executeQuery = true
    ) {
        $dql = '
            SELECT ot
            FROM Claroline\CoreBundle\Entity\Tool\OrderedTool ot
            JOIN ot.tool t
            WHERE ot.workspace IS NULL
            AND ot.user IS NULL
            AND ot.type = :type
            AND t.name NOT IN (:excludedToolNames)
            AND t.isDisplayableInDesktop = true
            ORDER BY ot.order
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('type', $type);
        $query->setParameter('excludedToolNames', $excludedToolNames);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findLockedConfigurableDesktopOrderedToolsByTypeForAdmin(
        array $excludedToolNames,
        $type = 0,
        $executeQuery = true
    ) {
        $dql = '
            SELECT ot
            FROM Claroline\CoreBundle\Entity\Tool\OrderedTool ot
            JOIN ot.tool t
            WHERE ot.workspace IS NULL
            AND ot.user IS NULL
            AND ot.type = :type
            AND ot.locked = true
            AND t.name NOT IN (:excludedToolNames)
            AND t.isDisplayableInDesktop = true
            ORDER BY ot.order
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('excludedToolNames', $excludedToolNames);
        $query->setParameter('type', $type);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findOrderedToolsByToolAndUser(
        Tool $tool,
        User $user,
        $type = 0,
        $executeQuery = true
    ) {
        $dql = '
            SELECT ot
            FROM Claroline\CoreBundle\Entity\Tool\OrderedTool ot
            WHERE ot.tool = :tool
            AND ot.user = :user
            AND ot.workspace IS NULL
            AND ot.type = :type
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('tool', $tool);
        $query->setParameter('user', $user);
        $query->setParameter('type', $type);

        return $executeQuery ? $query->getResult() : $query;
    }

    /**
     * Returns the ordered tools locked by admin.
     *
     * @return array[OrderedTool]
     */
    public function findOrderedToolsLockedByAdmin($orderedToolType = 0)
    {
        $dql = "
            SELECT ot
            FROM Claroline\CoreBundle\Entity\Tool\OrderedTool ot
            JOIN ot.tool t
            WHERE ot.user IS NULL
            AND ot.workspace IS NULL
            AND ot.type = :type
            AND ot.locked = true
            AND t.isDisplayableInDesktop = true
            ORDER BY ot.order
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('type', $orderedToolType);

        return $query->getResult();
    }

    public function findDuplicatedOldOrderedToolsByUsers()
    {
        $dql = "
            SELECT ot1
            FROM Claroline\CoreBundle\Entity\Tool\OrderedTool ot1
            WHERE ot1.user IS NOT NULL
            AND EXISTS (
                SELECT ot2
                FROM Claroline\CoreBundle\Entity\Tool\OrderedTool ot2
                WHERE ot1.tool = ot2.tool
                AND ot1.user = ot2.user
            )
            ORDER BY ot1.id
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function findDuplicatedOldOrderedToolsByWorkspaces()
    {
        $dql = "
            SELECT ot1
            FROM Claroline\CoreBundle\Entity\Tool\OrderedTool ot1
            WHERE ot1.workspace IS NOT NULL
            AND EXISTS (
                SELECT ot2
                FROM Claroline\CoreBundle\Entity\Tool\OrderedTool ot2
                WHERE ot1.tool = ot2.tool
                AND ot1.workspace = ot2.workspace
            )
            ORDER BY ot1.id
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }
}
