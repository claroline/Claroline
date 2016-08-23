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
use Claroline\CoreBundle\Entity\Workspace\Workspace;
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
            AND htc.workspace IS NULL
            ORDER BY htc.tabOrder ASC
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function findAdminDesktopHomeTabConfigsByRoles(array $roleNames)
    {
        $dql = "
            SELECT htc
            FROM Claroline\CoreBundle\Entity\Home\HomeTabConfig htc
            JOIN htc.homeTab ht
            LEFT JOIN ht.roles r
            WHERE htc.type = 'admin_desktop'
            AND htc.user IS NULL
            AND htc.workspace IS NULL
            AND (
                r.id IS NULL
                OR r.name IN (:roleNames)
            )
            ORDER BY htc.tabOrder ASC
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('roleNames', $roleNames);

        return $query->getResult();
    }

    public function findVisibleAdminDesktopHomeTabConfigsByRoles(array $roleNames)
    {
        $dql = "
            SELECT htc
            FROM Claroline\CoreBundle\Entity\Home\HomeTabConfig htc
            JOIN htc.homeTab ht
            LEFT JOIN ht.roles r
            WHERE htc.type = 'admin_desktop'
            AND htc.visible = true
            AND htc.user IS NULL
            AND htc.workspace IS NULL
            AND (
                r.id IS NULL
                OR r.name IN (:roleNames)
            )
            ORDER BY htc.tabOrder ASC
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('roleNames', $roleNames);

        return $query->getResult();
    }

    public function findAdminWorkspaceHomeTabConfigs()
    {
        $dql = "
            SELECT htc
            FROM Claroline\CoreBundle\Entity\Home\HomeTabConfig htc
            WHERE htc.type = 'admin_workspace'
            AND htc.workspace IS NULL
            AND htc.user IS NULL
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
            AND htc.workspace IS NULL
            AND htc.type = 'desktop'
            ORDER BY htc.tabOrder ASC
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);

        return $query->getResult();
    }

    public function findWorkspaceHomeTabConfigsByWorkspace(Workspace $workspace)
    {
        $dql = "
            SELECT htc
            FROM Claroline\CoreBundle\Entity\Home\HomeTabConfig htc
            WHERE htc.workspace = :workspace
            AND htc.type = 'workspace'
            AND htc.user IS NULL
            ORDER BY htc.tabOrder ASC
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);

        return $query->getResult();
    }

    public function findVisibleAdminDesktopHomeTabConfigs()
    {
        $dql = "
            SELECT htc, ht
            FROM Claroline\CoreBundle\Entity\Home\HomeTabConfig htc
            JOIN htc.homeTab ht
            WHERE htc.type = 'admin_desktop'
            AND htc.user IS NULL
            AND htc.workspace IS NULL
            AND htc.visible = true
            ORDER BY htc.tabOrder ASC
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function findVisibleAdminWorkspaceHomeTabConfigs()
    {
        $dql = "
            SELECT htc, ht
            FROM Claroline\CoreBundle\Entity\Home\HomeTabConfig htc
            JOIN htc.homeTab ht
            WHERE htc.type = 'admin_workspace'
            AND htc.workspace IS NULL
            AND htc.user IS NULL
            AND htc.visible = true
            ORDER BY htc.tabOrder ASC
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function findVisibleDesktopHomeTabConfigsByUser(User $user)
    {
        $dql = "
            SELECT htc, ht
            FROM Claroline\CoreBundle\Entity\Home\HomeTabConfig htc
            JOIN htc.homeTab ht
            WHERE htc.user = :user
            AND htc.workspace IS NULL
            AND htc.type = 'desktop'
            AND htc.visible = true
            ORDER BY htc.tabOrder ASC
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);

        return $query->getResult();
    }

    public function findVisibleWorkspaceHomeTabConfigsByWorkspace(Workspace $workspace)
    {
        $dql = "
            SELECT htc, ht
            FROM Claroline\CoreBundle\Entity\Home\HomeTabConfig htc
            JOIN htc.homeTab ht
            WHERE htc.workspace = :workspace
            AND htc.user IS NULL
            AND htc.type = 'workspace'
            AND htc.visible = true
            ORDER BY htc.tabOrder ASC
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);

        return $query->getResult();
    }

    public function findVisibleWorkspaceHomeTabConfigsByWorkspaceAndRoles(
        Workspace $workspace,
        array $roleNames
    ) {
        $dql = "
            SELECT htc, ht
            FROM Claroline\CoreBundle\Entity\Home\HomeTabConfig htc
            JOIN htc.homeTab ht
            LEFT JOIN ht.roles r
            WHERE htc.workspace = :workspace
            AND htc.user IS NULL
            AND htc.type = 'workspace'
            AND htc.visible = true
            AND (
                r.id IS NULL
                OR r.name IN (:roleNames)
            )
            ORDER BY htc.tabOrder ASC
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);
        $query->setParameter('roleNames', $roleNames);

        return $query->getResult();
    }

    public function findWorkspaceHomeTabConfigsByAdmin()
    {
        $dql = "
            SELECT htc
            FROM Claroline\CoreBundle\Entity\Home\HomeTabConfig htc
            WHERE htc.type = 'admin_workspace'
            AND htc.workspace IS NOT NULL
            AND htc.user IS NULL
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
            AND htc.workspace IS NULL
            AND htc.type = 'desktop'
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);

        return $query->getSingleResult();
    }

    public function findOrderOfLastWorkspaceHomeTabByWorkspace(Workspace $workspace)
    {
        $dql = "
            SELECT MAX(htc.tabOrder) AS order_max
            FROM Claroline\CoreBundle\Entity\Home\HomeTabConfig htc
            WHERE htc.workspace = :workspace
            AND htc.user IS NULL
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
            AND htc.workspace IS NULL
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
            AND htc.user IS NULL
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getSingleResult();
    }

    public function updateAdminHomeTabOrder(
        $type,
        $homeTabOrder,
        $newHomeTabOrder
    ) {
        $dql = "
            UPDATE Claroline\CoreBundle\Entity\Home\HomeTabConfig htc
            SET htc.tabOrder = :newHomeTabOrder
            WHERE htc.type = :type
            AND htc.user IS NULL
            AND htc.workspace IS NULL
            AND htc.tabOrder = :homeTabOrder
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('type', $type);
        $query->setParameter('homeTabOrder', $homeTabOrder);
        $query->setParameter('newHomeTabOrder', $newHomeTabOrder);

        return $query->execute();
    }

    public function updateHomeTabOrderByUser(
        User $user,
        $homeTabOrder,
        $newHomeTabOrder
    ) {
        $dql = "
            UPDATE Claroline\CoreBundle\Entity\Home\HomeTabConfig htc
            SET htc.tabOrder = :newHomeTabOrder
            WHERE htc.type = 'desktop'
            AND htc.user = :user
            AND htc.workspace IS NULL
            AND htc.tabOrder = :homeTabOrder
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);
        $query->setParameter('homeTabOrder', $homeTabOrder);
        $query->setParameter('newHomeTabOrder', $newHomeTabOrder);

        return $query->execute();
    }

    public function updateHomeTabOrderByWorkspace(
        Workspace $workspace,
        $homeTabOrder,
        $newHomeTabOrder
    ) {
        $dql = "
            UPDATE Claroline\CoreBundle\Entity\Home\HomeTabConfig htc
            SET htc.tabOrder = :newHomeTabOrder
            WHERE htc.type = 'workspace'
            AND htc.workspace = :workspace
            AND htc.user IS NULL
            AND htc.tabOrder = :homeTabOrder
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);
        $query->setParameter('homeTabOrder', $homeTabOrder);
        $query->setParameter('newHomeTabOrder', $newHomeTabOrder);

        return $query->execute();
    }

    public function findHomeTabConfigsByWorkspaceAndHomeTabs(
        Workspace $workspace,
        array $homeTabs
    ) {
        $dql = "
            SELECT htc
            FROM Claroline\CoreBundle\Entity\Home\HomeTabConfig htc
            WHERE htc.workspace = :workspace
            AND htc.type = 'workspace'
            AND htc.user IS NULL
            AND htc.homeTab IN (:homeTabs)
            ORDER BY htc.tabOrder ASC
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);
        $query->setParameter('homeTabs', $homeTabs);

        return $query->getResult();
    }

    public function checkHomeTabVisibilityByIdAndWorkspace(
        $homeTabId,
        Workspace $workspace
    ) {
        $dql = "
            SELECT htc.id
            FROM Claroline\CoreBundle\Entity\Home\HomeTabConfig htc
            WHERE htc.homeTab = :homeTabId
            AND htc.workspace = :workspace
            AND htc.user IS NULL
            AND htc.visible = true
            ORDER BY htc.tabOrder ASC
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);
        $query->setParameter('homeTabId', $homeTabId);

        return $query->getResult();
    }

    public function findOneVisibleWorkspaceUserHTC(HomeTab $homeTab, User $user)
    {
        $dql = "
            SELECT htc
            FROM Claroline\CoreBundle\Entity\Home\HomeTabConfig htc
            WHERE htc.homeTab = :homeTab
            AND htc.workspace = :workspace
            AND htc.user = :user
            AND htc.type = 'workspace_user'
            AND htc.visible = true
            ORDER BY htc.tabOrder ASC
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('homeTab', $homeTab);
        $query->setParameter('workspace', $homeTab->getWorkspace());
        $query->setParameter('user', $user);

        return $query->getOneOrNullResult();
    }

    public function findVisibleWorkspaceUserHTCsByUser(User $user)
    {
        $dql = "
            SELECT htc, ht
            FROM Claroline\CoreBundle\Entity\Home\HomeTabConfig htc
            JOIN htc.homeTab ht
            WHERE htc.workspace IS NOT NULL
            AND htc.user = :user
            AND htc.type = 'workspace_user'
            AND htc.visible = true
            ORDER BY htc.tabOrder ASC
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);

        return $query->getResult();
    }

    public function findOrderOfLastWorkspaceUserHomeTabByUser(User $user)
    {
        $dql = "
            SELECT MAX(htc.tabOrder) AS order_max
            FROM Claroline\CoreBundle\Entity\Home\HomeTabConfig htc
            WHERE htc.workspace IS NOT NULL
            AND htc.user = :user
            AND htc.type = 'workspace_user'
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);

        return $query->getSingleResult();
    }

    public function findHomeTabConfigsByType($type)
    {
        $dql = "
            SELECT htc
            FROM Claroline\CoreBundle\Entity\Home\HomeTabConfig htc
            WHERE htc.type = :type
            ORDER BY htc.tabOrder ASC
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('type', $type);

        return $query->getResult();
    }

    public function findHomeTabConfigsByUserAndType(User $user, $type)
    {
        $dql = "
            SELECT htc
            FROM Claroline\CoreBundle\Entity\Home\HomeTabConfig htc
            WHERE htc.type = :type
            AND htc.user = :user
            ORDER BY htc.tabOrder ASC
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);
        $query->setParameter('type', $type);

        return $query->getResult();
    }

    public function findOrderOfLastHomeTabByType($type)
    {
        $dql = "
            SELECT MAX(htc.tabOrder) AS order_max
            FROM Claroline\CoreBundle\Entity\Home\HomeTabConfig htc
            WHERE htc.type = :type
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('type', $type);

        return $query->getSingleResult();
    }

    public function findOrderOfLastHomeTabByUserAndType(User $user, $type)
    {
        $dql = "
            SELECT MAX(htc.tabOrder) AS order_max
            FROM Claroline\CoreBundle\Entity\Home\HomeTabConfig htc
            WHERE htc.type = :type
            AND htc.user = :user
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);
        $query->setParameter('type', $type);

        return $query->getSingleResult();
    }
}
