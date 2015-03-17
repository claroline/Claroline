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
    )
    {
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
    )
    {
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
    )
    {
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
}