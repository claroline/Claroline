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
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class WidgetHomeTabConfigRepository extends EntityRepository implements ContainerAwareInterface
{
    private $bundles = [];
    private $container;

    const LEFT_JOIN_PLUGIN = '
        LEFT JOIN whtc.widgetInstance instance
        JOIN instance.widget widget
        LEFT JOIN widget.plugin plugin
    ';

    const WHERE_PLUGIN_ENABLED = '
        (CONCAT(plugin.vendorName, plugin.bundleName) IN (:bundles)
        OR widget.plugin is NULL)
    ';

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->bundles = $this->container->get('claroline.manager.plugin_manager')->getEnabled(true);
    }

    public function findAdminWidgetConfigs(HomeTab $homeTab)
    {
        $dql = "
            SELECT whtc
            FROM Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig whtc"
            .self::LEFT_JOIN_PLUGIN.'
            WHERE whtc.homeTab = :homeTab
            AND whtc.user IS NULL
            AND whtc.workspace IS NULL'
            .' AND '.self::WHERE_PLUGIN_ENABLED.'
            ORDER BY whtc.widgetOrder ASC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('homeTab', $homeTab);
        $query->setParameter('bundles', $this->bundles);

        return $query->getResult();
    }

    public function findVisibleAdminWidgetConfigs(HomeTab $homeTab)
    {
        $dql = "
            SELECT whtc
            FROM Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig whtc"
            .self::LEFT_JOIN_PLUGIN.'
            WHERE whtc.homeTab = :homeTab
            AND whtc.user IS NULL
            AND whtc.workspace IS NULL'
            .' AND '.self::WHERE_PLUGIN_ENABLED.'
            AND whtc.visible = true
            ORDER BY whtc.widgetOrder ASC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('homeTab', $homeTab);
        $query->setParameter('bundles', $this->bundles);

        return $query->getResult();
    }

    public function findWidgetConfigsByUser(HomeTab $homeTab, User $user)
    {
        $dql = "
            SELECT whtc
            FROM Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig whtc"
            .self::LEFT_JOIN_PLUGIN.'
            WHERE whtc.homeTab = :homeTab
            AND whtc.user = :user
            AND whtc.workspace IS NULL'
            .' AND '.self::WHERE_PLUGIN_ENABLED."
            AND whtc.type = 'desktop'
            ORDER BY whtc.widgetOrder ASC
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('homeTab', $homeTab);
        $query->setParameter('user', $user);
        $query->setParameter('bundles', $this->bundles);

        return $query->getResult();
    }

    public function findVisibleWidgetConfigsByUser(HomeTab $homeTab, User $user)
    {
        $dql = "
            SELECT whtc
            FROM Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig whtc"
            .self::LEFT_JOIN_PLUGIN."
            WHERE whtc.homeTab = :homeTab
            AND whtc.user = :user
            AND whtc.workspace IS NULL
            AND whtc.type = 'desktop'
            AND whtc.visible = true"
            .' AND '.self::WHERE_PLUGIN_ENABLED.'
            ORDER BY whtc.widgetOrder ASC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('homeTab', $homeTab);
        $query->setParameter('user', $user);
        $query->setParameter('bundles', $this->bundles);

        return $query->getResult();
    }

    public function findWidgetConfigsByWorkspace(
        HomeTab $homeTab,
        Workspace $workspace
    ) {
        $dql = "
            SELECT whtc
            FROM Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig whtc"
            .self::LEFT_JOIN_PLUGIN."
            WHERE whtc.homeTab = :homeTab
            AND whtc.workspace = :workspace
            AND whtc.user IS NULL
            AND whtc.type = 'workspace'"
            .' AND '.self::WHERE_PLUGIN_ENABLED.'
            ORDER BY whtc.widgetOrder ASC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('homeTab', $homeTab);
        $query->setParameter('workspace', $workspace);
        $query->setParameter('bundles', $this->bundles);

        return $query->getResult();
    }

    public function findVisibleWidgetConfigsByWorkspace(
        HomeTab $homeTab,
        Workspace $workspace
    ) {
        $dql = "
            SELECT whtc
            FROM Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig whtc"
            .self::LEFT_JOIN_PLUGIN."
            WHERE whtc.homeTab = :homeTab
            AND whtc.workspace = :workspace
            AND whtc.user IS NULL
            AND whtc.type = 'workspace'
            AND whtc.visible = true"
            .' AND '.self::WHERE_PLUGIN_ENABLED.'
            ORDER BY whtc.widgetOrder ASC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('homeTab', $homeTab);
        $query->setParameter('workspace', $workspace);
        $query->setParameter('bundles', $this->bundles);

        return $query->getResult();
    }

    public function findVisibleWidgetConfigsByTabIdAndWorkspace(
        $homeTabId,
        Workspace $workspace
    ) {
        $dql = "
            SELECT whtc
            FROM Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig whtc"
            .self::LEFT_JOIN_PLUGIN."
            JOIN Claroline\CoreBundle\Entity\Home\HomeTabConfig htc
            WITH htc.homeTab = :homeTabId
            WHERE whtc.homeTab = :homeTabId
            AND htc.visible = true
            AND whtc.workspace = :workspace
            AND whtc.user IS NULL
            AND whtc.type = 'workspace'
            AND whtc.visible = true"
            .' AND '.self::WHERE_PLUGIN_ENABLED.'
            ORDER BY whtc.widgetOrder ASC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('homeTabId', $homeTabId);
        $query->setParameter('workspace', $workspace);
        $query->setParameter('bundles', $this->bundles);

        return $query->getResult();
    }

    public function findVisibleWidgetConfigByWidgetIdAndTabIdAndWorkspace(
        $widgetId,
        $homeTabId,
        Workspace $workspace
    ) {
        $dql = "
            SELECT whtc
            FROM Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig whtc"
            .self::LEFT_JOIN_PLUGIN."
            JOIN Claroline\CoreBundle\Entity\Home\HomeTabConfig htc
            WITH htc.homeTab = :homeTabId
            WHERE whtc.homeTab = :homeTabId"
            .' AND '.self::WHERE_PLUGIN_ENABLED."
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
        $query->setParameter('bundles', $this->bundles);

        return $query->getOneOrNullResult();
    }

    public function updateAdminWidgetHomeTabConfig(HomeTab $homeTab, $widgetOrder)
    {
        $dql = "
            UPDATE Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig whtc"
            .self::LEFT_JOIN_PLUGIN.'
            SET whtc.widgetOrder = whtc.widgetOrder - 1
            WHERE whtc.homeTab = :homeTab'
            .' AND '.self::WHERE_PLUGIN_ENABLED.'
            AND whtc.user IS NULL
            AND whtc.workspace IS NULL
            AND whtc.widgetOrder > :widgetOrder
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('homeTab', $homeTab);
        $query->setParameter('widgetOrder', $widgetOrder);
        $query->setParameter('bundles', $this->bundles);

        return $query->execute();
    }

    public function updateWidgetHomeTabConfigByUser(
        HomeTab $homeTab,
        $widgetOrder,
        User $user
    ) {
        $dql = "
            UPDATE Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig whtc"
            .self::LEFT_JOIN_PLUGIN.'
            SET whtc.widgetOrder = whtc.widgetOrder - 1
            WHERE whtc.homeTab = :homeTab'
            .' AND '.self::WHERE_PLUGIN_ENABLED.'
            AND whtc.user = :user
            AND whtc.workspace IS NULL
            AND whtc.widgetOrder > :widgetOrder
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('homeTab', $homeTab);
        $query->setParameter('user', $user);
        $query->setParameter('widgetOrder', $widgetOrder);
        $query->setParameter('bundles', $this->bundles);

        return $query->execute();
    }

    public function updateWidgetHomeTabConfigByWorkspace(
        HomeTab $homeTab,
        $widgetOrder,
        Workspace $workspace
    ) {
        $dql = "
            UPDATE Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig whtc"
            .self::LEFT_JOIN_PLUGIN.'
            SET whtc.widgetOrder = whtc.widgetOrder - 1
            WHERE whtc.homeTab = :homeTab'
            .' AND '.self::WHERE_PLUGIN_ENABLED.'
            AND whtc.workspace = :workspace
            AND whtc.user IS NULL
            AND whtc.widgetOrder > :widgetOrder
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('homeTab', $homeTab);
        $query->setParameter('workspace', $workspace);
        $query->setParameter('widgetOrder', $widgetOrder);
        $query->setParameter('bundles', $this->bundles);

        return $query->execute();
    }

    public function updateAdminWidgetOrder(
        HomeTab $homeTab,
        $widgetOrder,
        $newWidgetOrder
    ) {
        $dql = "
            UPDATE Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig whtc"
            .self::LEFT_JOIN_PLUGIN.'
            SET whtc.widgetOrder = :newWidgetOrder
            WHERE whtc.homeTab = :homeTab'
            .' AND '.self::WHERE_PLUGIN_ENABLED.'
            AND whtc.user IS NULL
            AND whtc.workspace IS NULL
            AND whtc.widgetOrder = :widgetOrder
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('homeTab', $homeTab);
        $query->setParameter('widgetOrder', $widgetOrder);
        $query->setParameter('newWidgetOrder', $newWidgetOrder);
        $query->setParameter('bundles', $this->bundles);

        return $query->execute();
    }

    public function updateWidgetOrderByUser(
        HomeTab $homeTab,
        $widgetOrder,
        $newWidgetOrder,
        User $user
    ) {
        $dql = "
            UPDATE Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig whtc"
            .self::LEFT_JOIN_PLUGIN.'
            SET whtc.widgetOrder = :newWidgetOrder
            WHERE whtc.homeTab = :homeTab'
            .' AND '.self::WHERE_PLUGIN_ENABLED.'
            AND whtc.user = :user
            AND whtc.workspace IS NULL
            AND whtc.widgetOrder = :widgetOrder
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('homeTab', $homeTab);
        $query->setParameter('widgetOrder', $widgetOrder);
        $query->setParameter('newWidgetOrder', $newWidgetOrder);
        $query->setParameter('user', $user);
        $query->setParameter('bundles', $this->bundles);

        return $query->execute();
    }

    public function updateWidgetOrderByWorkspace(
        HomeTab $homeTab,
        $widgetOrder,
        $newWidgetOrder,
        Workspace $workspace
    ) {
        $dql = "
            UPDATE Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig whtc"
            .self::LEFT_JOIN_PLUGIN.'
            SET whtc.widgetOrder = :newWidgetOrder
            WHERE whtc.homeTab = :homeTab'
            .' AND '.self::WHERE_PLUGIN_ENABLED.'
            AND whtc.workspace = :workspace
            AND whtc.user IS NULL
            AND whtc.widgetOrder = :widgetOrder
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('homeTab', $homeTab);
        $query->setParameter('widgetOrder', $widgetOrder);
        $query->setParameter('newWidgetOrder', $newWidgetOrder);
        $query->setParameter('workspace', $workspace);
        $query->setParameter('bundles', $this->bundles);

        return $query->execute();
    }

    public function findUserAdminWidgetHomeTabConfig(
        HomeTab $homeTab,
        WidgetInstance $widgetInstance,
        User $user
    ) {
        $dql = "
            SELECT whtc
            FROM Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig whtc"
            .self::LEFT_JOIN_PLUGIN.'
            WHERE whtc.homeTab = :homeTab'
            .' AND '.self::WHERE_PLUGIN_ENABLED."
            AND whtc.widgetInstance = :widgetInstance
            AND whtc.user = :user
            AND whtc.workspace IS NULL
            AND whtc.type = 'admin_desktop'
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('homeTab', $homeTab);
        $query->setParameter('widgetInstance', $widgetInstance);
        $query->setParameter('user', $user);
        $query->setParameter('bundles', $this->bundles);

        return $query->getResult();
    }

    public function findWidgetHomeTabConfigsByHomeTabAndType(HomeTab $homeTab, $type)
    {
        $dql = "
            SELECT whtc
            FROM Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig whtc"
            .self::LEFT_JOIN_PLUGIN.'
            WHERE whtc.homeTab = :homeTab'
            .' AND '.self::WHERE_PLUGIN_ENABLED.'
            AND whtc.type = :type
            ORDER BY whtc.widgetOrder ASC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('homeTab', $homeTab);
        $query->setParameter('type', $type);
        $query->setParameter('bundles', $this->bundles);

        return $query->getResult();
    }
}
