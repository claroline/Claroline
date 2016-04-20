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
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class WidgetInstanceRepository extends EntityRepository implements ContainerAwareInterface
{
    const LEFT_JOIN_PLUGIN = '
        JOIN wdc.widget widget
        LEFT JOIN widget.plugin plugin
    ';

    const WHERE_PLUGIN_ENABLED = '
        (CONCAT(plugin.vendorName, plugin.bundleName) IN (:bundles)
        OR widget.plugin is NULL)
    ';

    private $bundles = [];

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->bundles = $this->container->get('claroline.manager.plugin_manager')->getEnabled(true);
    }

    public function findAdminDesktopWidgetInstance(array $excludedWidgetInstances)
    {
        $dql = "
            SELECT wdc
            FROM Claroline\CoreBundle\Entity\Widget\WidgetInstance wdc"
            .self::LEFT_JOIN_PLUGIN.'
            WHERE wdc.isAdmin = true
            AND wdc.isDesktop = true
            AND wdc NOT IN (:excludedWidgetInstances)'
            .' AND '.self::WHERE_PLUGIN_ENABLED;

        $query = $this->_em->createQuery($dql);
        $query->setParameter('excludedWidgetInstances', $excludedWidgetInstances);
        $query->setParameter('bundles', $this->bundles);

        return $query->getResult();
    }

    public function findAdminWorkspaceWidgetInstance(array $excludedWidgetInstances)
    {
        $dql = "
            SELECT wdc
            FROM Claroline\CoreBundle\Entity\Widget\WidgetInstance wdc"
            .self::LEFT_JOIN_PLUGIN.'
            WHERE wdc.isAdmin = true
            AND wdc.isDesktop = false
            AND wdc NOT IN (:excludedWidgetInstances)'
            .' AND '.self::WHERE_PLUGIN_ENABLED;

        $query = $this->_em->createQuery($dql);
        $query->setParameter('excludedWidgetInstances', $excludedWidgetInstances);
        $query->setParameter('bundles', $this->bundles);

        return $query->getResult();
    }

    public function findDesktopWidgetInstance(
        User $user,
        array $excludedWidgetInstances
    ) {
        $dql = "
            SELECT wdc
            FROM Claroline\CoreBundle\Entity\Widget\WidgetInstance wdc"
            .self::LEFT_JOIN_PLUGIN.'
            WHERE wdc.user = :user
            AND wdc.isAdmin = false
            AND wdc.isDesktop = true
            AND wdc NOT IN (:excludedWidgetInstances)'
            .' AND '.self::WHERE_PLUGIN_ENABLED;
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);
        $query->setParameter('excludedWidgetInstances', $excludedWidgetInstances);
        $query->setParameter('bundles', $this->bundles);

        return $query->getResult();
    }

    public function findWorkspaceWidgetInstance(
        Workspace $workspace,
        array $excludedWidgetInstances
    ) {
        $dql = "
            SELECT wdc
            FROM Claroline\CoreBundle\Entity\Widget\WidgetInstance wdc"
            .self::LEFT_JOIN_PLUGIN.'
            WHERE wdc.workspace = :workspace
            AND wdc.isAdmin = false
            AND wdc.isDesktop = false
            AND wdc NOT IN (:excludedWidgetInstances)'
            .' AND '.self::WHERE_PLUGIN_ENABLED;
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);
        $query->setParameter('excludedWidgetInstances', $excludedWidgetInstances);
        $query->setParameter('bundles', $this->bundles);

        return $query->getResult();
    }
}
