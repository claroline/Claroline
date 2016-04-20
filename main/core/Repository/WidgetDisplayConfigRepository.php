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

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\ORM\EntityRepository;

class WidgetDisplayConfigRepository extends EntityRepository
{
    public function findWidgetDisplayConfigsByUserAndWidgets(
        User $user,
        array $widgetInstances,
        $executeQuery = true
    ) {
        $dql = '
            SELECT wdc
            FROM Claroline\CoreBundle\Entity\Widget\WidgetDisplayConfig wdc
            WHERE wdc.user = :user
            AND wdc.workspace IS NULL
            AND wdc.widgetInstance IN (:widgetInstances)
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);
        $query->setParameter('widgetInstances', $widgetInstances);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findAdminWidgetDisplayConfigsByWidgets(
        array $widgetInstances,
        $executeQuery = true
    ) {
        $dql = '
            SELECT wdc
            FROM Claroline\CoreBundle\Entity\Widget\WidgetDisplayConfig wdc
            WHERE wdc.user IS NULL
            AND wdc.workspace IS NULL
            AND wdc.widgetInstance IN (:widgetInstances)
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('widgetInstances', $widgetInstances);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findWidgetDisplayConfigsByWorkspaceAndWidgets(
        Workspace $workspace,
        array $widgetInstances,
        $executeQuery = true
    ) {
        $dql = '
            SELECT wdc
            FROM Claroline\CoreBundle\Entity\Widget\WidgetDisplayConfig wdc
            WHERE wdc.workspace = :workspace
            AND wdc.user IS NULL
            AND wdc.widgetInstance IN (:widgetInstances)
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);
        $query->setParameter('widgetInstances', $widgetInstances);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findWidgetDisplayConfigsByWidgetsForAdmin(
        array $widgetInstances,
        $executeQuery = true
    ) {
        $dql = '
            SELECT wdc
            FROM Claroline\CoreBundle\Entity\Widget\WidgetDisplayConfig wdc
            WHERE wdc.workspace IS NULL
            AND wdc.user IS NULL
            AND wdc.widgetInstance IN (:widgetInstances)
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('widgetInstances', $widgetInstances);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findWidgetDisplayConfigsByWorkspaceAndWidgetHTCs(
        Workspace $workspace,
        array $widgetHomeTabConfigs,
        $executeQuery = true
    ) {
        $dql = '
            SELECT wdc
            FROM Claroline\CoreBundle\Entity\Widget\WidgetDisplayConfig wdc
            WHERE wdc.workspace = :workspace
            AND wdc.user IS NULL
            AND wdc.widgetInstance IN (
                SELECT wi
                FROM Claroline\CoreBundle\Entity\Widget\WidgetInstance wi
                WHERE wi.isAdmin = false
                AND wi.isDesktop = false
                AND wi.workspace = :workspace
                AND wi.user IS NULL
                AND EXISTS (
                    SELECT whtc
                    FROM Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig whtc
                    WHERE whtc IN (:widgetHomeTabConfigs)
                    AND whtc.widgetInstance = wi
                    AND whtc.workspace = :workspace
                    AND whtc.user IS NULL
                    AND whtc.type = :type
                )
            )
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);
        $query->setParameter('type', 'workspace');
        $query->setParameter('widgetHomeTabConfigs', $widgetHomeTabConfigs);

        return $executeQuery ? $query->getResult() : $query;
    }
}
