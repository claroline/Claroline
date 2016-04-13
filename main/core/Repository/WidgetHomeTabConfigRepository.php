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

use Claroline\CoreBundle\Entity\Home\HomeTab;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\ORM\EntityRepository;

class WidgetHomeTabConfigRepository extends EntityRepository
{
    public function findAdminWidgetConfigs(HomeTab $homeTab)
    {
        $dql = "
            SELECT whtc
            FROM Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig whtc
            WHERE whtc.homeTab = :homeTab
            AND whtc.user IS NULL
            AND whtc.workspace IS NULL
            ORDER BY whtc.widgetOrder ASC
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('homeTab', $homeTab);

        return $query->getResult();
    }

    public function findVisibleAdminWidgetConfigs(HomeTab $homeTab)
    {
        $dql = "
            SELECT whtc
            FROM Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig whtc
            WHERE whtc.homeTab = :homeTab
            AND whtc.user IS NULL
            AND whtc.workspace IS NULL
            AND whtc.visible = true
            ORDER BY whtc.widgetOrder ASC
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('homeTab', $homeTab);

        return $query->getResult();
    }

    public function findWidgetConfigsByUser(HomeTab $homeTab, User $user)
    {
        $dql = "
            SELECT whtc
            FROM Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig whtc
            WHERE whtc.homeTab = :homeTab
            AND whtc.user = :user
            AND whtc.workspace IS NULL
            AND whtc.type = 'desktop'
            ORDER BY whtc.widgetOrder ASC
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('homeTab', $homeTab);
        $query->setParameter('user', $user);

        return $query->getResult();
    }

    public function findVisibleWidgetConfigsByUser(HomeTab $homeTab, User $user)
    {
        $dql = "
            SELECT whtc
            FROM Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig whtc
            WHERE whtc.homeTab = :homeTab
            AND whtc.user = :user
            AND whtc.workspace IS NULL
            AND whtc.type = 'desktop'
            AND whtc.visible = true
            ORDER BY whtc.widgetOrder ASC
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('homeTab', $homeTab);
        $query->setParameter('user', $user);

        return $query->getResult();
    }

    public function findWidgetConfigsByWorkspace(
        HomeTab $homeTab,
        Workspace $workspace
    ) {
        $dql = "
            SELECT whtc
            FROM Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig whtc
            WHERE whtc.homeTab = :homeTab
            AND whtc.workspace = :workspace
            AND whtc.user IS NULL
            AND whtc.type = 'workspace'
            ORDER BY whtc.widgetOrder ASC
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('homeTab', $homeTab);
        $query->setParameter('workspace', $workspace);

        return $query->getResult();
    }

    public function findVisibleWidgetConfigsByWorkspace(
        HomeTab $homeTab,
        Workspace $workspace
    ) {
        $dql = "
            SELECT whtc
            FROM Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig whtc
            WHERE whtc.homeTab = :homeTab
            AND whtc.workspace = :workspace
            AND whtc.user IS NULL
            AND whtc.type = 'workspace'
            AND whtc.visible = true
            ORDER BY whtc.widgetOrder ASC
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('homeTab', $homeTab);
        $query->setParameter('workspace', $workspace);

        return $query->getResult();
    }

    public function findVisibleWidgetConfigsByTabIdAndWorkspace(
        $homeTabId,
        Workspace $workspace
    ) {
        $dql = "
            SELECT whtc
            FROM Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig whtc
            JOIN Claroline\CoreBundle\Entity\Home\HomeTabConfig htc
            WITH htc.homeTab = :homeTabId
            WHERE whtc.homeTab = :homeTabId
            AND htc.visible = true
            AND whtc.workspace = :workspace
            AND whtc.user IS NULL
            AND whtc.type = 'workspace'
            AND whtc.visible = true
            ORDER BY whtc.widgetOrder ASC
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('homeTabId', $homeTabId);
        $query->setParameter('workspace', $workspace);

        return $query->getResult();
    }

    public function findVisibleWidgetConfigByWidgetIdAndTabIdAndWorkspace(
        $widgetId,
        $homeTabId,
        Workspace $workspace
    ) {
        $dql = "
            SELECT whtc
            FROM Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig whtc
            JOIN Claroline\CoreBundle\Entity\Home\HomeTabConfig htc
            WITH htc.homeTab = :homeTabId
            WHERE whtc.homeTab = :homeTabId
            AND htc.visible = true
            AND whtc.workspace = :workspace
            AND whtc.user IS NULL
            AND whtc.type = 'workspace'
            AND whtc.visible = true
            AND whtc.widgetInstance = :widgetId
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('widgetId', $widgetId);
        $query->setParameter('homeTabId', $homeTabId);
        $query->setParameter('workspace', $workspace);

        return $query->getOneOrNullResult();
    }

    public function updateAdminWidgetHomeTabConfig(HomeTab $homeTab, $widgetOrder)
    {
        $dql = "
            UPDATE Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig whtc
            SET whtc.widgetOrder = whtc.widgetOrder - 1
            WHERE whtc.homeTab = :homeTab
            AND whtc.user IS NULL
            AND whtc.workspace IS NULL
            AND whtc.widgetOrder > :widgetOrder
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('homeTab', $homeTab);
        $query->setParameter('widgetOrder', $widgetOrder);

        return $query->execute();
    }

    public function updateWidgetHomeTabConfigByUser(
        HomeTab $homeTab,
        $widgetOrder,
        User $user
    ) {
        $dql = "
            UPDATE Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig whtc
            SET whtc.widgetOrder = whtc.widgetOrder - 1
            WHERE whtc.homeTab = :homeTab
            AND whtc.user = :user
            AND whtc.workspace IS NULL
            AND whtc.widgetOrder > :widgetOrder
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('homeTab', $homeTab);
        $query->setParameter('user', $user);
        $query->setParameter('widgetOrder', $widgetOrder);

        return $query->execute();
    }

    public function updateWidgetHomeTabConfigByWorkspace(
        HomeTab $homeTab,
        $widgetOrder,
        Workspace $workspace
    ) {
        $dql = "
            UPDATE Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig whtc
            SET whtc.widgetOrder = whtc.widgetOrder - 1
            WHERE whtc.homeTab = :homeTab
            AND whtc.workspace = :workspace
            AND whtc.user IS NULL
            AND whtc.widgetOrder > :widgetOrder
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('homeTab', $homeTab);
        $query->setParameter('workspace', $workspace);
        $query->setParameter('widgetOrder', $widgetOrder);

        return $query->execute();
    }

    public function updateAdminWidgetOrder(
        HomeTab $homeTab,
        $widgetOrder,
        $newWidgetOrder
    ) {
        $dql = "
            UPDATE Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig whtc
            SET whtc.widgetOrder = :newWidgetOrder
            WHERE whtc.homeTab = :homeTab
            AND whtc.user IS NULL
            AND whtc.workspace IS NULL
            AND whtc.widgetOrder = :widgetOrder
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('homeTab', $homeTab);
        $query->setParameter('widgetOrder', $widgetOrder);
        $query->setParameter('newWidgetOrder', $newWidgetOrder);

        return $query->execute();
    }

    public function updateWidgetOrderByUser(
        HomeTab $homeTab,
        $widgetOrder,
        $newWidgetOrder,
        User $user
    ) {
        $dql = "
            UPDATE Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig whtc
            SET whtc.widgetOrder = :newWidgetOrder
            WHERE whtc.homeTab = :homeTab
            AND whtc.user = :user
            AND whtc.workspace IS NULL
            AND whtc.widgetOrder = :widgetOrder
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('homeTab', $homeTab);
        $query->setParameter('widgetOrder', $widgetOrder);
        $query->setParameter('newWidgetOrder', $newWidgetOrder);
        $query->setParameter('user', $user);

        return $query->execute();
    }

    public function updateWidgetOrderByWorkspace(
        HomeTab $homeTab,
        $widgetOrder,
        $newWidgetOrder,
        Workspace $workspace
    ) {
        $dql = "
            UPDATE Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig whtc
            SET whtc.widgetOrder = :newWidgetOrder
            WHERE whtc.homeTab = :homeTab
            AND whtc.workspace = :workspace
            AND whtc.user IS NULL
            AND whtc.widgetOrder = :widgetOrder
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('homeTab', $homeTab);
        $query->setParameter('widgetOrder', $widgetOrder);
        $query->setParameter('newWidgetOrder', $newWidgetOrder);
        $query->setParameter('workspace', $workspace);

        return $query->execute();
    }

    public function findUserAdminWidgetHomeTabConfig(
        HomeTab $homeTab,
        WidgetInstance $widgetInstance,
        User $user
    ) {
        $dql = "
            SELECT whtc
            FROM Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig whtc
            WHERE whtc.homeTab = :homeTab
            AND whtc.widgetInstance = :widgetInstance
            AND whtc.user = :user
            AND whtc.workspace IS NULL
            AND whtc.type = 'admin_desktop'
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('homeTab', $homeTab);
        $query->setParameter('widgetInstance', $widgetInstance);
        $query->setParameter('user', $user);

        return $query->getResult();
    }

    public function findWidgetHomeTabConfigsByHomeTabAndType(HomeTab $homeTab, $type)
    {
        $dql = "
            SELECT whtc
            FROM Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig whtc
            WHERE whtc.homeTab = :homeTab
            AND whtc.type = :type
            ORDER BY whtc.widgetOrder ASC
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('homeTab', $homeTab);
        $query->setParameter('type', $type);

        return $query->getResult();
    }
}
