<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Entity\Home\HomeTab;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Doctrine\ORM\EntityRepository;

class HomeTabConfigRepository extends EntityRepository
{
    public function findAdminDesktopHomeTabConfigs()
    {
        $dql = "
            SELECT htc
            FROM Claroline\CoreBundle\Entity\Home\HomeTabConfig htc
            WHERE htc.type = 'admin_desktop'
            AND htc.user IS NULL
            ORDER BY htc.tabOrder ASC
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function findAdminWorkspaceHomeTabConfigs()
    {
        $dql = "
            SELECT htc
            FROM Claroline\CoreBundle\Entity\Home\HomeTabConfig htc
            WHERE htc.type = 'admin_workspace'
            AND htc.workspace IS NULL
            ORDER BY htc.tabOrder ASC
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function findAdminDesktopHomeTabConfigByHomeTab(HomeTab $homeTab)
    {
        $dql = "
            SELECT htc
            FROM Claroline\CoreBundle\Entity\Home\HomeTabConfig htc
            WHERE htc.homeTab = :homeTab
            AND htc.user IS NULL
            AND htc.workspace IS NULL
            AND htc.type = 'admin_desktop'
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('homeTab', $homeTab);

        return $query->getSingleResult();
    }

    public function findDesktopHomeTabConfigsByUser(User $user)
    {
        $dql = "
            SELECT htc
            FROM Claroline\CoreBundle\Entity\Home\HomeTabConfig htc
            WHERE htc.user = :user
            AND htc.type = 'desktop'
            ORDER BY htc.tabOrder ASC
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);

        return $query->getResult();
    }

    public function findWorkspaceHomeTabConfigsByWorkspace(AbstractWorkspace $workspace)
    {
        $dql = "
            SELECT htc
            FROM Claroline\CoreBundle\Entity\Home\HomeTabConfig htc
            WHERE htc.workspace = :workspace
            AND htc.type = 'workspace'
            ORDER BY htc.tabOrder ASC
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);

        return $query->getResult();
    }

    public function findVisibleAdminDesktopHomeTabConfigs()
    {
        $dql = "
            SELECT htc
            FROM Claroline\CoreBundle\Entity\Home\HomeTabConfig htc
            WHERE htc.type = 'admin_desktop'
            AND htc.user IS NULL
            AND htc.visible = true
            ORDER BY htc.tabOrder ASC
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function findVisibleAdminWorkspaceHomeTabConfigs()
    {
        $dql = "
            SELECT htc
            FROM Claroline\CoreBundle\Entity\Home\HomeTabConfig htc
            JOIN htc.homeTab ht
            WHERE htc.type = 'admin_workspace'
            AND htc.workspace IS NULL
            AND htc.visible = true
            ORDER BY htc.tabOrder ASC
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function findVisibleDesktopHomeTabConfigsByUser(User $user)
    {
        $dql = "
            SELECT htc
            FROM Claroline\CoreBundle\Entity\Home\HomeTabConfig htc
            WHERE htc.user = :user
            AND htc.type = 'desktop'
            AND htc.visible = true
            ORDER BY htc.tabOrder ASC
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);

        return $query->getResult();
    }

    public function findVisibleWorkspaceHomeTabConfigsByWorkspace(AbstractWorkspace $workspace)
    {
        $dql = "
            SELECT htc
            FROM Claroline\CoreBundle\Entity\Home\HomeTabConfig htc
            WHERE htc.workspace = :workspace
            AND htc.type = 'workspace'
            AND htc.visible = true
            ORDER BY htc.tabOrder ASC
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);

        return $query->getResult();
    }

    public function findWorkspaceHomeTabConfigsByAdmin()
    {
        $dql = "
            SELECT htc
            FROM Claroline\CoreBundle\Entity\Home\HomeTabConfig htc
            WHERE htc.type = 'admin_workspace'
            AND htc.workspace IS NOT NULL
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function findOrderOfLastDesktopHomeTabByUser(User $user)
    {
        $dql = "
            SELECT MAX(htc.tabOrder) AS order_max
            FROM Claroline\CoreBundle\Entity\Home\HomeTabConfig htc
            WHERE htc.user = :user
            AND htc.type = 'desktop'
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);

        return $query->getSingleResult();
    }

    public function findOrderOfLastWorkspaceHomeTabByWorkspace(AbstractWorkspace $workspace)
    {
        $dql = "
            SELECT MAX(htc.tabOrder) AS order_max
            FROM Claroline\CoreBundle\Entity\Home\HomeTabConfig htc
            WHERE htc.workspace = :workspace
            AND htc.type = 'workspace'
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);

        return $query->getSingleResult();
    }

    public function findOrderOfLastAdminDesktopHomeTab()
    {
        $dql = "
            SELECT MAX(htc.tabOrder) AS order_max
            FROM Claroline\CoreBundle\Entity\Home\HomeTabConfig htc
            WHERE htc.type = 'admin_desktop'
            AND htc.user IS NULL
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getSingleResult();
    }

    public function findOrderOfLastAdminWorkspaceHomeTab()
    {
        $dql = "
            SELECT MAX(htc.tabOrder) AS order_max
            FROM Claroline\CoreBundle\Entity\Home\HomeTabConfig htc
            WHERE htc.type = 'admin_workspace'
            AND htc.workspace IS NULL
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getSingleResult();
    }

    public function updateAdminDesktopOrder($tabOrder)
    {
        $dql = "
            UPDATE Claroline\CoreBundle\Entity\Home\HomeTabConfig htc
            SET htc.tabOrder = htc.tabOrder - 1
            WHERE htc.type = 'admin_desktop'
            AND htc.user IS NULL
            AND htc.tabOrder > :tabOrder
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('tabOrder', $tabOrder);

        return $query->execute();
    }

    public function updateAdminWorkspaceOrder($tabOrder)
    {
        $dql = "
            UPDATE Claroline\CoreBundle\Entity\Home\HomeTabConfig htc
            SET htc.tabOrder = htc.tabOrder - 1
            WHERE htc.type = 'admin_workspace'
            AND htc.workspace IS NULL
            AND htc.tabOrder > :tabOrder
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('tabOrder', $tabOrder);

        return $query->execute();
    }

    public function updateDesktopOrder(User $user, $tabOrder)
    {
        $dql = "
            UPDATE Claroline\CoreBundle\Entity\Home\HomeTabConfig htc
            SET htc.tabOrder = htc.tabOrder - 1
            WHERE htc.type = 'desktop'
            AND htc.user = :user
            AND htc.tabOrder > :tabOrder
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('tabOrder', $tabOrder);
        $query->setParameter('user', $user);

        return $query->execute();
    }

    public function updateWorkspaceOrder(AbstractWorkspace $workspace, $tabOrder)
    {
        $dql = "
            UPDATE Claroline\CoreBundle\Entity\Home\HomeTabConfig htc
            SET htc.tabOrder = htc.tabOrder - 1
            WHERE htc.type = 'workspace'
            AND htc.workspace = :workspace
            AND htc.tabOrder > :tabOrder
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('tabOrder', $tabOrder);
        $query->setParameter('workspace', $workspace);

        return $query->execute();
    }
}