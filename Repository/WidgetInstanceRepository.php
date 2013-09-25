<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Doctrine\ORM\EntityRepository;

class WidgetInstanceRepository extends EntityRepository
{
    public function findAdminDesktopWidgetInstance(array $excludedWidgetInstances)
    {
        $dql = "
            SELECT wdc
            FROM Claroline\CoreBundle\Entity\Widget\WidgetInstance wdc
            WHERE wdc.isAdmin = true
            AND wdc.isDesktop = true
            AND wdc NOT IN (:excludedWidgetInstances)
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('excludedWidgetInstances', $excludedWidgetInstances);

        return $query->getResult();
    }

    public function findAdminWorkspaceWidgetInstance(array $excludedWidgetInstances)
    {
        $dql = "
            SELECT wdc
            FROM Claroline\CoreBundle\Entity\Widget\WidgetInstance wdc
            WHERE wdc.isAdmin = true
            AND wdc.isDesktop = false
            AND wdc NOT IN (:excludedWidgetInstances)
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('excludedWidgetInstances', $excludedWidgetInstances);

        return $query->getResult();
    }

    public function findDesktopWidgetInstance(
        User $user,
        array $excludedWidgetInstances
    )
    {
        $dql = "
            SELECT wdc
            FROM Claroline\CoreBundle\Entity\Widget\WidgetInstance wdc
            WHERE wdc.user = :user
            AND wdc.isAdmin = false
            AND wdc.isDesktop = true
            AND wdc NOT IN (:excludedWidgetInstances)
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);
        $query->setParameter('excludedWidgetInstances', $excludedWidgetInstances);

        return $query->getResult();
    }

    public function findWorkspaceWidgetInstance(
        AbstractWorkspace $workspace,
        array $excludedWidgetInstances
    )
    {
        $dql = "
            SELECT wdc
            FROM Claroline\CoreBundle\Entity\Widget\WidgetInstance wdc
            WHERE wdc.workspace = :workspace
            AND wdc.isAdmin = false
            AND wdc.isDesktop = false
            AND wdc NOT IN (:excludedWidgetInstances)
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);
        $query->setParameter('excludedWidgetInstances', $excludedWidgetInstances);

        return $query->getResult();
    }
}
