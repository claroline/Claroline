<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Repository\Tool;

use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\PluginManager;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ToolRepository extends ServiceEntityRepository
{
    /** @var array */
    private $bundles;

    /**
     * ToolRepository constructor.
     */
    public function __construct(ManagerRegistry $registry, PluginManager $pluginManager)
    {
        $this->bundles = $pluginManager->getEnabled();

        parent::__construct($registry, Tool::class);
    }

    /**
     * Returns the non-visible tools in a workspace.
     *
     * @return Tool[]
     */
    public function findUndisplayedToolsByWorkspace(Workspace $workspace)
    {
        $query = $this->_em->createQuery('
            SELECT tool
            FROM Claroline\CoreBundle\Entity\Tool\Tool tool
            LEFT JOIN tool.plugin p
            WHERE tool.isDisplayableInWorkspace = true
            AND tool NOT IN (
                SELECT tool_2 
                FROM Claroline\CoreBundle\Entity\Tool\OrderedTool ot
                JOIN ot.tool tool_2
                JOIN ot.workspace ws
                WHERE ws.id = :workspaceId
                AND tool.isDisplayableInWorkspace = true
            )
            AND (
                CONCAT(p.vendorName, p.bundleName) IN (:bundles)
                OR tool.plugin is NULL
            )
        ');

        $query->setParameter('workspaceId', $workspace->getId());
        $query->setParameter('bundles', $this->bundles);

        return $query->getResult();
    }

    /**
     * Returns the number of tools visible in a workspace.
     *
     * @return int
     */
    public function countDisplayedToolsByWorkspace(Workspace $workspace)
    {
        $query = $this->_em->createQuery('
            SELECT count(tool)
            FROM Claroline\CoreBundle\Entity\Tool\OrderedTool ot
            JOIN ot.tool tool
            JOIN ot.workspace ws
            LEFT JOIN tool.plugin p
            WHERE ws.id = :workspaceId
            AND (
                CONCAT(p.vendorName, p.bundleName) IN (:bundles)
                OR tool.plugin is NULL
            )
        ');

        $query->setParameter('workspaceId', $workspace->getId());
        $query->setParameter('bundles', $this->bundles);

        return $query->getSingleScalarResult();
    }
}
