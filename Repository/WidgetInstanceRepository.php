<?php

namespace Claroline\CoreBundle\Repository;

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
}
