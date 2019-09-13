<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\API\Finder\Tool;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Claroline\CoreBundle\Entity\Tool\AdminTool;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\PluginManager;
use Doctrine\ORM\QueryBuilder;

class AdminToolFinder extends AbstractFinder
{
    /** @var PluginManager */
    private $pluginManager;
    /** @var PlatformConfigurationHandler */
    private $configHandler;

    /**
     * AdminToolFinder constructor.
     *
     * @param PluginManager                $pluginManager
     * @param PlatformConfigurationHandler $configHandler
     */
    public function __construct(PluginManager $pluginManager, PlatformConfigurationHandler $configHandler)
    {
        $this->pluginManager = $pluginManager;
        $this->configHandler = $configHandler;
    }

    public function getClass()
    {
        return AdminTool::class;
    }

    public function configureQueryBuilder(
        QueryBuilder $qb,
        array $searches = [],
        array $sortBy = null,
        array $options = ['count' => false, 'page' => 0, 'limit' => -1]
    ) {
        $bundles = $this->pluginManager->getEnabled(true);

        // only grab tools from enabled plugins
        $qb->leftJoin('obj.plugin', 'p');
        $qb->andWhere($qb->expr()->orX(
            $qb->expr()->in('CONCAT(p.vendorName, p.bundleName)', ':bundles'),
            $qb->expr()->isNull('p')
        ));
        $qb->setParameter('bundles', $bundles);

        // exclude disabled tools
        $disabledAdmin = $this->configHandler->getParameter('security.disabled_admin_tools');
        if (!empty($disabledAdmin)) {
            $qb->andWhere('obj.name NOT IN (:disabledTools)');
            $qb->setParameter('disabledTools', $disabledAdmin);
        }

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'roles':
                    $qb->join('obj.roles', 'r');
                    $qb->andWhere("r.name IN (:{$filterName})");
                    $qb->setParameter($filterName, is_array($filterValue) ? $filterValue : [$filterValue]);
                    break;
                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
            }
        }

        return $qb;
    }

    public function getFilters()
    {
        return [
            '$defaults' => [],
        ];
    }
}
