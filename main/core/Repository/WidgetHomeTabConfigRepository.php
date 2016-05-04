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
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function findAdminWidgetConfigs(HomeTab $homeTab)
    {
        return $this->buildBaseQuery($homeTab)
            ->andWhere('whtc.workspace IS NULL')
            ->andWhere('whtc.user IS NULL')
            ->getQuery()
            ->getResult();
    }

    public function findVisibleAdminWidgetConfigs(HomeTab $homeTab)
    {
        return $this->buildBaseQuery($homeTab)
            ->andWhere('whtc.workspace IS NULL')
            ->andWhere('whtc.user IS NULL')
            ->andWhere('whtc.visible = true')
            ->getQuery()
            ->getResult();
    }

    public function findWidgetConfigsByUser(HomeTab $homeTab, User $user)
    {
        return $this->buildBaseQuery($homeTab)
            ->andWhere('whtc.workspace IS NULL')
            ->andWhere('whtc.user = :user')
            ->andWhere("whtc.type = 'desktop'")
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    public function findVisibleWidgetConfigsByUser(HomeTab $homeTab, User $user)
    {
        return $this->buildBaseQuery($homeTab)
            ->andWhere('whtc.workspace IS NULL')
            ->andWhere('whtc.user = :user')
            ->andWhere("whtc.type = 'desktop'")
            ->andWhere('whtc.visible = true')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    public function findWidgetConfigsByWorkspace(HomeTab $homeTab, Workspace $workspace)
    {
        return $this->buildBaseQuery($homeTab)
            ->andWhere('whtc.workspace = :workspace')
            ->andWhere('whtc.user IS NULL')
            ->andWhere("whtc.type = 'workspace'")
            ->setParameter('workspace', $workspace)
            ->getQuery()
            ->getResult();
    }

    public function findVisibleWidgetConfigsByWorkspace(HomeTab $homeTab, Workspace $workspace)
    {
        return $this->buildBaseQuery($homeTab)
            ->andWhere('whtc.workspace = :workspace')
            ->andWhere('whtc.user IS NULL')
            ->andWhere("whtc.type = 'workspace'")
            ->andWhere('whtc.visible = true')
            ->setParameter('workspace', $workspace)
            ->getQuery()
            ->getResult();
    }

    public function findVisibleWidgetConfigsByTabIdAndWorkspace($homeTabId, Workspace $workspace)
    {
        return $this->buildBaseQuery($homeTabId)
            ->join(
                'Claroline\CoreBundle\Entity\Home\HomeTabConfig',
                'htc',
                'WITH',
                'htc.homeTab = :homeTabId'
            )
            ->where('htc.homeTab = :homeTabId')
            ->andWhere('htc.visible = true')
            ->andWhere('whtc.workspace = :workspace')
            ->andWhere('whtc.user IS NULL')
            ->andWhere("whtc.type = 'workspace'")
            ->andWhere('whtc.visible = true')
            ->setParameter('workspace', $workspace)
            ->getQuery()
            ->getResult();
    }

    public function findVisibleWidgetConfigByWidgetIdAndTabIdAndWorkspace(
        $widgetId,
        $homeTabId,
        Workspace $workspace
    ) {
        return $this->buildBaseQuery($homeTabId)
            ->join(
                'Claroline\CoreBundle\Entity\Home\HomeTabConfig',
                'htc',
                'WITH',
                'htc.homeTab = :homeTab'
            )
            ->where('htc.homeTab = :homeTab')
            ->andWhere('htc.visible = true')
            ->andWhere('whtc.workspace = :workspace')
            ->andWhere('whtc.user IS NULL')
            ->andWhere("whtc.type = 'workspace'")
            ->andWhere('whtc.visible = true')
            ->andWhere('whtc.widgetInstance = :widgetId')
            ->setParameter('workspace', $workspace)
            ->setParameter('widgetId', $widgetId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findUserAdminWidgetHomeTabConfig(
        HomeTab $homeTab,
        WidgetInstance $widgetInstance,
        User $user
    ) {
        return $this->buildBaseQuery($homeTab)
            ->andWhere('whtc.user = :user')
            ->andWhere('whtc.workspace IS NULL')
            ->andWhere("whtc.type = 'admin_desktop'")
            ->andWhere('whtc.widgetInstance = :widgetInstance')
            ->setParameter('user', $user)
            ->setParameter('widgetInstance', $widgetInstance)
            ->getQuery()
            ->getResult();
    }

    public function findWidgetHomeTabConfigsByHomeTabAndType(HomeTab $homeTab, $type)
    {
        return $this->buildBaseQuery($homeTab)
            ->andWhere('whtc.type = :type')
            ->setParameter('type', $type)
            ->getQuery()
            ->getResult();
    }

    public function updateAdminWidgetHomeTabConfig(HomeTab $homeTab, $widgetOrder)
    {
        return $this->buildBaseQuery($homeTab, true)
            ->set('whtc.widgetOrder', 'whtc.widgetOrder - 1')
            ->where('whtc.user IS NULL')
            ->andWhere('whtc.workspace IS NULL')
            ->andWhere('whtc.widgetOrder > :widgetOrder')
            ->setParameter('widgetOrder', $widgetOrder)
            ->getQuery()
            ->execute();
    }

    public function updateWidgetHomeTabConfigByUser(
        HomeTab $homeTab,
        $widgetOrder,
        User $user
    ) {
        return $this->buildBaseQuery($homeTab, true)
            ->set('whtc.widgetOrder', 'whtc.widgetOrder - 1')
            ->where('whtc.user = :user')
            ->andWhere('whtc.workspace IS NULL')
            ->andWhere('whtc.widgetOrder > :widgetOrder')
            ->setParameter('user', $user)
            ->setParameter('widgetOrder', $widgetOrder)
            ->getQuery()
            ->execute();
    }

    public function updateWidgetHomeTabConfigByWorkspace(
        HomeTab $homeTab,
        $widgetOrder,
        Workspace $workspace
    ) {
        return $this->buildBaseQuery($homeTab, true)
            ->set('whtc.widgetOrder', 'whtc.widgetOrder - 1')
            ->where('whtc.user IS NULL')
            ->andWhere('whtc.workspace = :workspace')
            ->andWhere('whtc.widgetOrder > :widgetOrder')
            ->setParameter('workspace', $workspace)
            ->getQuery()
            ->execute();
    }

    public function updateAdminWidgetOrder(
        HomeTab $homeTab,
        $widgetOrder,
        $newWidgetOrder
    ) {
        return $this->buildBaseQuery($homeTab, true)
            ->set('whtc.widgetOrder', ':newWidgetOrder')
            ->where('whtc.user IS NULL')
            ->andWhere('whtc.workspace IS NULL')
            ->andWhere('whtc.widgetOrder = :widgetOrder')
            ->setParameter('widgetOrder', $widgetOrder)
            ->setParameter('newWidgetOrder', $newWidgetOrder)
            ->getQuery()
            ->execute();
    }

    public function updateWidgetOrderByUser(
        HomeTab $homeTab,
        $widgetOrder,
        $newWidgetOrder,
        User $user
    ) {
        return $this->buildBaseQuery($homeTab, true)
            ->set('whtc.widgetOrder', ':newWidgetOrder')
            ->where('whtc.user = :user')
            ->andWhere('whtc.workspace IS NULL')
            ->andWhere('whtc.widgetOrder = :widgetOrder')
            ->setParameter('user', $user)
            ->setParameter('widgetOrder', $widgetOrder)
            ->setParameter('newWidgetOrder', $newWidgetOrder)
            ->getQuery()
            ->execute();
    }

    public function updateWidgetOrderByWorkspace(
        HomeTab $homeTab,
        $widgetOrder,
        $newWidgetOrder,
        Workspace $workspace
    ) {
        return $this->buildBaseQuery($homeTab, true)
            ->set('whtc.widgetOrder', ':newWidgetOrder')
            ->where('whtc.user IS NULL')
            ->andWhere('whtc.workspace = :workspace')
            ->andWhere('whtc.widgetOrder = :widgetOrder')
            ->setParameter('workspace', $workspace)
            ->setParameter('widgetOrder', $widgetOrder)
            ->setParameter('newWidgetOrder', $newWidgetOrder)
            ->getQuery()
            ->execute();
    }

    private function buildBaseQuery($homeTab, $isUpdate = false)
    {
        $qb = $this->createQueryBuilder('whtc');

        if ($isUpdate) {
            $qb->update('whtc');
        } else {
            $qb->select('whtc');
        }

        $qb->leftJoin('whtc.widgetInstance', 'instance')
            ->join('instance.widget', 'widget')
            ->leftJoin('widget.plugin', 'plugin')
            ->where('whtc.homeTab = :homeTab')
            ->andWhere($qb->expr()->orX(
                'CONCAT(plugin.vendorName, plugin.bundleName) IN (:bundles)',
                'widget.plugin IS NULL'
            ))
            ->orderBy('whtc.widgetOrder', 'ASC');

        $bundles = $this->container
            ->get('claroline.manager.plugin_manager')
            ->getEnabled(true);

        return $qb->setParameters([
            'homeTab' => $homeTab,
            'bundles' => $bundles,
        ]);
    }
}
