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
use Claroline\CoreBundle\Manager\PluginManager;
use Doctrine\ORM\QueryBuilder;

class AdminToolFinder extends AbstractFinder
{
    /** @var PluginManager */
    private $pluginManager;

    /**
     * AdminToolFinder constructor.
     */
    public function __construct(PluginManager $pluginManager)
    {
        $this->pluginManager = $pluginManager;
    }

    public static function getClass(): string
    {
        return AdminTool::class;
    }

    public function configureQueryBuilder(
        QueryBuilder $qb,
        array $searches = [],
        array $sortBy = null,
        array $options = ['count' => false, 'page' => 0, 'limit' => -1]
    ) {
        $bundles = $this->pluginManager->getEnabled();

        // only grab tools from enabled plugins
        $qb->leftJoin('obj.plugin', 'p');
        $qb->andWhere($qb->expr()->orX(
            $qb->expr()->in('CONCAT(p.vendorName, p.bundleName)', ':bundles'),
            $qb->expr()->isNull('p')
        ));
        $qb->setParameter('bundles', $bundles);

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
}
