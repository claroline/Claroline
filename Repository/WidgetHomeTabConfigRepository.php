<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Entity\Home\HomeTab;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
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
        AbstractWorkspace $workspace
    )
    {
        $dql = "
            SELECT whtc
            FROM Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig whtc
            WHERE whtc.homeTab = :homeTab
            AND whtc.workspace = :workspace
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
        AbstractWorkspace $workspace
    )
    {
        $dql = "
            SELECT whtc
            FROM Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig whtc
            WHERE whtc.homeTab = :homeTab
            AND whtc.workspace = :workspace
            AND whtc.type = 'workspace'
            AND whtc.visible = true
            ORDER BY whtc.widgetOrder ASC
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('homeTab', $homeTab);
        $query->setParameter('workspace', $workspace);

        return $query->getResult();
    }

    public function findOrderOfLastWidgetInAdminHomeTab(HomeTab $homeTab)
    {
        $dql = "
            SELECT MAX(whtc.widgetOrder) AS order_max
            FROM Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig whtc
            WHERE whtc.homeTab = :homeTab
            AND whtc.user IS NULL
            AND whtc.workspace IS NULL
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('homeTab', $homeTab);

        return $query->getSingleResult();
    }

    public function findOrderOfLastWidgetInHomeTabByUser(
        HomeTab $homeTab,
        User $user
    )
    {
        $dql = "
            SELECT MAX(whtc.widgetOrder) AS order_max
            FROM Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig whtc
            WHERE whtc.homeTab = :homeTab
            AND whtc.user = :user
            AND whtc.type = 'desktop'
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('homeTab', $homeTab);
        $query->setParameter('user', $user);

        return $query->getSingleResult();
    }

    public function findOrderOfLastWidgetInHomeTabByWorkspace(
        HomeTab $homeTab,
        AbstractWorkspace $workspace
    )
    {
        $dql = "
            SELECT MAX(whtc.widgetOrder) AS order_max
            FROM Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig whtc
            WHERE whtc.homeTab = :homeTab
            AND whtc.workspace = :workspace
            AND whtc.type = 'workspace'
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('homeTab', $homeTab);
        $query->setParameter('workspace', $workspace);

        return $query->getSingleResult();
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
    )
    {
        $dql = "
            UPDATE Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig whtc
            SET whtc.widgetOrder = whtc.widgetOrder - 1
            WHERE whtc.homeTab = :homeTab
            AND whtc.user = :user
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
        AbstractWorkspace $workspace
    )
    {
        $dql = "
            UPDATE Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig whtc
            SET whtc.widgetOrder = whtc.widgetOrder - 1
            WHERE whtc.homeTab = :homeTab
            AND whtc.workspace = :workspace
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
    )
    {
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
    )
    {
        $dql = "
            UPDATE Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig whtc
            SET whtc.widgetOrder = :newWidgetOrder
            WHERE whtc.homeTab = :homeTab
            AND whtc.user = :user
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
        AbstractWorkspace $workspace
    )
    {
        $dql = "
            UPDATE Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig whtc
            SET whtc.widgetOrder = :newWidgetOrder
            WHERE whtc.homeTab = :homeTab
            AND whtc.workspace = :workspace
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
    )
    {
        $dql = "
            SELECT whtc
            FROM Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig whtc
            WHERE whtc.homeTab = :homeTab
            AND whtc.widgetInstance = :widgetInstance
            AND whtc.user = :user
            AND whtc.type = 'admin_desktop'
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('homeTab', $homeTab);
        $query->setParameter('widgetInstance', $widgetInstance);
        $query->setParameter('user', $user);

        return $query->getResult();
    }
}