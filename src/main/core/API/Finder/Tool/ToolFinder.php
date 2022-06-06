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
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Manager\PluginManager;
use Claroline\CoreBundle\Manager\Tool\ToolManager;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

class ToolFinder extends AbstractFinder
{
    /** @var PluginManager */
    private $pluginManager;

    public function __construct(PluginManager $pluginManager)
    {
        $this->pluginManager = $pluginManager;
    }

    public static function getClass(): string
    {
        return Tool::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, array $options = ['count' => false, 'page' => 0, 'limit' => -1])
    {
        $bundles = $this->pluginManager->getEnabled();

        $qb->leftJoin('obj.plugin', 'p');
        $qb->andWhere($qb->expr()->orX(
            $qb->expr()->in('CONCAT(p.vendorName, p.bundleName)', ':bundles'),
            $qb->expr()->isNull('p')
        ));
        $qb->setParameter('bundles', $bundles);

        $otJoin = false;

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'user':
                    if (!$otJoin) {
                        $qb->join('Claroline\\CoreBundle\\Entity\\Tool\\OrderedTool', 'ot', Join::WITH, 'ot.tool = obj');
                        $otJoin = true;
                    }

                    $qb->andWhere('ot.user IS NULL AND ot.workspace IS NULL');
                    break;
                case 'workspace':
                    if (!$otJoin) {
                        $qb->join('Claroline\\CoreBundle\\Entity\\Tool\\OrderedTool', 'ot', Join::WITH, 'ot.tool = obj');
                        $otJoin = true;
                    }
                    $qb->join('ot.workspace', 'w');
                    $qb->andWhere("w.uuid = :{$filterName}");
                    $qb->andWhere('(w.model = false OR obj.name IN (:availableTools))');
                    $qb->setParameter($filterName, $filterValue);
                    $qb->setParameter('availableTools', ToolManager::WORKSPACE_MODEL_TOOLS);
                    break;
                case 'roles':
                    if (!$otJoin) {
                        $qb->join('Claroline\\CoreBundle\\Entity\\Tool\\OrderedTool', 'ot', Join::WITH, 'ot.tool = obj');
                        $otJoin = true;
                    }
                    $qb->join('ot.rights', 'r');
                    $qb->join('r.role', 'rr');
                    $qb->andWhere('BIT_AND(r.mask, 1) = 1');
                    $qb->andWhere("rr.name IN (:{$filterName})");
                    $qb->setParameter($filterName, is_array($filterValue) ? $filterValue : [$filterValue]);
                    break;
                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
            }
        }

        return $qb;
    }
}
