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

use Claroline\CoreBundle\Entity\Tab\HomeTab;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class WidgetInstanceConfigRepository extends EntityRepository implements ContainerAwareInterface
{
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function findAdminWidgetConfigs(HomeTab $homeTab)
    {
        return $this->buildSelectQuery($homeTab)
            ->andWhere('whtc.workspace IS NULL')
            ->andWhere('whtc.user IS NULL')
            ->getQuery()
            ->getResult();
    }

    private function buildSelectQuery($homeTab)
    {
        $qb = $this->createQueryBuilder('whtc');

        return $qb
            ->select('whtc')
            ->leftJoin('whtc.widgetInstance', 'instance')
            ->join('instance.widget', 'widget')
            ->leftJoin('widget.plugin', 'plugin')
            ->where('whtc.homeTab = :homeTab')
            ->andWhere($qb->expr()->orX(
                'CONCAT(plugin.vendorName, plugin.bundleName) IN (:plugins)',
                'widget.plugin IS NULL'
            ))
            ->orderBy('whtc.widgetOrder', 'ASC')
            ->setParameters([
                'homeTab' => $homeTab,
                'plugins' => $this->getEnabledPlugins(),
            ]);
    }

    private function getEnabledPlugins()
    {
        return $this->container
            ->get('claroline.manager.plugin_manager')
            ->getEnabled(true);
    }
}
