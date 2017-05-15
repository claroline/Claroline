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
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function findAdminDesktopWidgetInstance(array $excludedWidgetInstances)
    {
        return $this->buildBaseQuery($excludedWidgetInstances)
            ->where('wdc.isAdmin = true')
            ->where('wdc.isDesktop = true')
            ->getQuery()
            ->getResult();
    }

    public function findAdminWorkspaceWidgetInstance(array $excludedWidgetInstances)
    {
        return $this->buildBaseQuery($excludedWidgetInstances)
            ->where('wdc.isAdmin = true')
            ->where('wdc.isDesktop = false')
            ->getQuery()
            ->getResult();
    }

    public function findDesktopWidgetInstance(User $user, array $excludedWidgetInstances)
    {
        return $this->buildBaseQuery($excludedWidgetInstances)
            ->where('wdc.user = :user')
            ->where('wdc.isAdmin = false')
            ->where('wdc.isDesktop = true')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    public function findWorkspaceWidgetInstance(Workspace $workspace, array $excludedWidgetInstances)
    {
        return $this->buildBaseQuery($excludedWidgetInstances)
            ->where('wdc.workspace = :workspace')
            ->where('wdc.isAdmin = false')
            ->where('wdc.isDesktop = false')
            ->setParameter('workspace', $workspace)
            ->getQuery()
            ->getResult();
    }

    private function buildBaseQuery(array $excludedWidgetInstances)
    {
        $bundles = $this->container
            ->get('claroline.manager.plugin_manager')
            ->getEnabled(true);

        $qb = $this->createQueryBuilder('wdc');

        return $qb
            ->select('wdc')
            ->join('wdc.widget', 'widget')
            ->leftJoin('widget.plugin', 'plugin')
            ->where('wdc NOT IN (:excludedWidgetInstances)')
            ->andWhere($qb->expr()->orX(
                'CONCAT(plugin.vendorName, plugin.bundleName) IN (:bundles)',
                'widget.plugin IS NULL'
            ))
            ->setParameters([
                'excludedWidgetInstances' => $excludedWidgetInstances,
                'bundles' => $bundles,
            ]);
    }

    /**
     * @param string $filter
     *
     * @return int
     */
    public function countWidgetInstances($filter = null)
    {
        $query = $this->createQueryBuilder('widget')
            ->select('COUNT(widget)');

        switch ($filter) {
            case 'workspace':
                $query->where('widget.isDesktop = FALSE');
                break;
            case 'desktop':
                $query->where('widget.isDesktop = TRUE');
                break;
            default:
                break;
        }

        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * Count widgets by type (with subtotal for windget in workspaces or on desktops).
     *
     * @return array
     */
    public function countByType()
    {
        return $this->createQueryBuilder('wi')
            ->select('wi.id, w.name, COUNT(w.id) AS total, SUM(CASE WHEN wi.isDesktop = TRUE THEN 1 ELSE 0 END) AS totalByDesktop, SUM(CASE WHEN wi.isDesktop = FALSE THEN 1 ELSE 0 END) AS totalByWorkspace')
            ->leftJoin('wi.widget', 'w')
            ->groupBy('w.id')
            ->orderBy('total', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
