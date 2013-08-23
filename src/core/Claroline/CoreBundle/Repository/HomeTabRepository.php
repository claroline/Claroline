<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Doctrine\ORM\EntityRepository;

class HomeTabRepository extends EntityRepository
{
    public function findWorkspaceHomeTabsByWorkspace(AbstractWorkspace $workspace)
    {
        $dql = "
            SELECT ht
            FROM Claroline\CoreBundle\Entity\Home\HomeTab ht
            WHERE ht.type = 'workspace'
            AND ht.workspace = :workspace
            ORDER BY ht.tabOrder ASC
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);

        return $query->getResult();
    }

    public function findAdminDesktopHomeTabs()
    {
        $dql = "
            SELECT ht
            FROM Claroline\CoreBundle\Entity\Home\HomeTab ht
            WHERE ht.type = 'desktop'
            AND ht.user IS NULL
            ORDER BY ht.tabOrder ASC
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function findAdminWorkspaceHomeTabs()
    {
        $dql = "
            SELECT ht
            FROM Claroline\CoreBundle\Entity\Home\HomeTab ht
            WHERE ht.type = 'workspace'
            AND ht.workspace IS NULL
            ORDER BY ht.tabOrder ASC
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function findDesktopHomeTabsByUser(User $user)
    {
        $dql = "
            SELECT ht
            FROM Claroline\CoreBundle\Entity\Home\HomeTab ht
            WHERE ht.type = 'desktop'
            AND ht.user = :user
            ORDER BY ht.tabOrder ASC
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);

        return $query->getResult();
    }

    public function findOrderOfLastDesktopHomeTabByUser(User $user)
    {
        $dql = "
            SELECT MAX(ht.tabOrder) AS order_max
            FROM Claroline\CoreBundle\Entity\Home\HomeTab ht
            WHERE ht.type = 'desktop'
            AND ht.user = :user
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);

        return $query->getSingleResult();
    }

    public function findOrderOfLastWorkspaceHomeTabByWorkspace(AbstractWorkspace $workspace)
    {
        $dql = "
            SELECT MAX(ht.tabOrder) AS order_max
            FROM Claroline\CoreBundle\Entity\Home\HomeTab ht
            WHERE ht.type = 'workspace'
            AND ht.workspace = :workspace
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);

        return $query->getSingleResult();
    }

    public function findOrderOfLastAdminDesktopHomeTab()
    {
        $dql = "
            SELECT MAX(ht.tabOrder) AS order_max
            FROM Claroline\CoreBundle\Entity\Home\HomeTab ht
            WHERE ht.type = 'desktop'
            AND ht.user IS NULL
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getSingleResult();
    }

    public function findOrderOfLastAdminWorkspaceHomeTab()
    {
        $dql = "
            SELECT MAX(ht.tabOrder) AS order_max
            FROM Claroline\CoreBundle\Entity\Home\HomeTab ht
            WHERE ht.type = 'workspace'
            AND ht.workspace IS NULL
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getSingleResult();
    }
}