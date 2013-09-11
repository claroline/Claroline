<?php

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

class DisplayConfigRepository extends EntityRepository
{
    public function findVisibleAdminDesktopWidgetDisplayConfig(array $excludedWidgets)
    {
        $dql = "
            SELECT wdc
            FROM Claroline\CoreBundle\Entity\Widget\DisplayConfig wdc
            WHERE wdc.parent IS NULL
            AND wdc.isVisible = true
            AND wdc.isDesktop = true
            AND wdc.widget NOT IN (:excludedWidgets)
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('excludedWidgets', $excludedWidgets);

        return $query->getResult();
    }

    public function findVisibleAdminWorkspaceWidgetDisplayConfig(array $excludedWidgets)
    {
        $dql = "
            SELECT wdc
            FROM Claroline\CoreBundle\Entity\Widget\DisplayConfig wdc
            WHERE wdc.parent IS NULL
            AND wdc.isVisible = true
            AND wdc.isDesktop = false
            AND wdc.widget NOT IN (:excludedWidgets)
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('excludedWidgets', $excludedWidgets);

        return $query->getResult();
    }
}