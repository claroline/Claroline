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

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ToolRepository extends EntityRepository implements ContainerAwareInterface
{
    private $bundles = [];
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->bundles = $this->container->get('claroline.manager.plugin_manager')->getEnabled(true);
    }

    /**
     * Returns the workspace tools visible by a set of roles.
     *
     * @param string[]  $roles
     * @param Workspace $workspace
     *
     * @return Tool[]
     *
     * @throws \RuntimeException
     */
    public function findDisplayedByRolesAndWorkspace(
        array $roles,
        Workspace $workspace,
        $orderedToolType = 0
    ) {
        if (count($roles) === 0) {
            return array();
        } else {
            $isAdmin = false;

            foreach ($roles as $role) {
                if ($role === 'ROLE_ADMIN' || $role === 'ROLE_WS_MANAGER_'.$workspace->getGuid()) {
                    $isAdmin = true;
                }
            }

            if (!$isAdmin) {
                $dql = '
                    SELECT t
                    FROM Claroline\CoreBundle\Entity\Tool\Tool t
                    JOIN t.orderedTools ot
                    JOIN ot.rights r
                    JOIN r.role rr
                    LEFT JOIN t.plugin p
                    WHERE ot.workspace = :workspace
                    AND ot.type = :type
                    AND rr.name IN (:roleNames)
                    AND BIT_AND(r.mask, 1) = 1
                    AND (
                        CONCAT(p.vendorName, p.bundleName) IN (:bundles)
                        OR p.id is NULL
                    )
                    ORDER BY ot.order
                ';
                $query = $this->_em->createQuery($dql);
                $query->setParameter('workspace', $workspace);
                $query->setParameter('roleNames', $roles);
                $query->setParameter('type', $orderedToolType);
                $query->setParameter('bundles', $this->bundles);
            } else {
                $dql = '
                    SELECT tool
                    FROM Claroline\CoreBundle\Entity\Tool\Tool tool
                    JOIN tool.orderedTools ot
                    LEFT JOIN tool.plugin p
                    WHERE ot.workspace = :workspace
                    AND tool.isDisplayableInWorkspace = true
                    AND (
                        CONCAT(p.vendorName, p.bundleName) IN (:bundles)
                        OR tool.plugin is NULL
                    )
                    ORDER BY ot.order
                ';

                $query = $this->_em->createQuery($dql);
                $query->setParameter('workspace', $workspace);
                $query->setParameter('bundles', $this->bundles);
            }

            return $query->getResult();
        }
    }

    /**
     * Returns the visible tools in a user's desktop.
     *
     * @param User $user
     *
     * @return array[Tool]
     */
    public function findDesktopDisplayedToolsByUser(User $user, $orderedToolType = 0)
    {
        $dql = "
            SELECT tool
            FROM Claroline\CoreBundle\Entity\Tool\Tool tool
            JOIN tool.orderedTools ot
            JOIN ot.user user
            LEFT JOIN tool.plugin p
            WHERE user.id = {$user->getId()}
            AND ot.type = :type
            AND ot.isVisibleInDesktop = true
            AND tool.isDisplayableInDesktop = true
            AND (
                CONCAT(p.vendorName, p.bundleName) IN (:bundles)
                OR tool.plugin is NULL
            )
            ORDER BY ot.order
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('type', $orderedToolType);
        $query->setParameter('bundles', $this->bundles);

        return $query->getResult();
    }

    /**
     * Returns the visible tools in a user's desktop.
     *
     * @param User $user
     *
     * @return array[Tool]
     */
    public function findDesktopDisplayedToolsWithExclusionByUser(
        User $user,
        array $excludedTools,
        $orderedToolType = 0
    ) {
        $dql = "
            SELECT tool
            FROM Claroline\CoreBundle\Entity\Tool\Tool tool
            JOIN tool.orderedTools ot
            JOIN ot.user user
            LEFT JOIN tool.plugin p
            WHERE user.id = {$user->getId()}
            AND ot.type = :type
            AND ot.isVisibleInDesktop = true
            AND tool.isDisplayableInDesktop = true
            AND tool NOT IN (:excludedTools)
            AND (
                CONCAT(p.vendorName, p.bundleName) IN (:bundles)
                OR tool.plugin is NULL
            )
            ORDER BY ot.order
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('type', $orderedToolType);
        $query->setParameter('excludedTools', $excludedTools);
        $query->setParameter('bundles', $this->bundles);

        return $query->getResult();
    }

    /**
     * Returns the non-visible tools in a user's desktop.
     *
     * @param User $user
     *
     * @return array[Tool]
     */
    public function findDesktopUndisplayedToolsByUser(User $user, $orderedToolType = 0)
    {
        $dql = "
            SELECT tool
            FROM Claroline\CoreBundle\Entity\Tool\Tool tool
            LEFT JOIN tool.plugin p
            WHERE tool NOT IN (
                SELECT tool_2
                FROM Claroline\CoreBundle\Entity\Tool\Tool tool_2
                JOIN tool_2.orderedTools ot_2
                JOIN ot_2.user user_2
                WHERE user_2.id = {$user->getId()}
                AND ot_2.type = :type
            )
            AND (
                CONCAT(p.vendorName, p.bundleName) IN (:bundles)
                OR tool.plugin is NULL
            )
            AND tool.isDisplayableInDesktop = true
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('type', $orderedToolType);
        $query->setParameter('bundles', $this->bundles);

        return $query->getResult();
    }

    /**
     * Returns the non-visible tools in a user's desktop in admin configuration.
     *
     * @return array[Tool]
     */
    public function findDesktopUndisplayedToolsByTypeForAdmin($orderedToolType = 0)
    {
        $dql = "
            SELECT tool
            FROM Claroline\CoreBundle\Entity\Tool\Tool tool
            LEFT JOIN tool.plugin p
            WHERE tool NOT IN (
                SELECT tool_2
                FROM Claroline\CoreBundle\Entity\Tool\Tool tool_2
                JOIN tool_2.orderedTools ot_2
                WHERE ot_2.user IS NULL
                AND ot_2.workspace IS NULL
                AND ot_2.type = :type
            )
            AND tool.isDisplayableInDesktop = true
            AND (
                CONCAT(p.vendorName, p.bundleName) IN (:bundles)
                OR tool.plugin is NULL
            )
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('type', $orderedToolType);
        $query->setParameter('bundles', $this->bundles);

        return $query->getResult();
    }

    /**
     * Returns the non-visible tools in a workspace.
     *
     * @param Workspace $workspace
     *
     * @return array[Tool]
     */
    public function findUndisplayedToolsByWorkspace(
        Workspace $workspace,
        $orderedToolType = 0
    ) {
        $dql = "
            SELECT tool
            FROM Claroline\CoreBundle\Entity\Tool\Tool tool
            LEFT JOIN tool.plugin p
            WHERE tool.isDisplayableInWorkspace = true
            AND tool NOT IN (
                SELECT tool_2 FROM Claroline\CoreBundle\Entity\Tool\Tool tool_2
                JOIN tool_2.orderedTools ot
                JOIN ot.workspace ws
                WHERE ws.id = {$workspace->getId()}
                AND tool.isDisplayableInWorkspace = true
                AND ot.type = :type
            )
            AND (
                CONCAT(p.vendorName, p.bundleName) IN (:bundles)
                OR tool.plugin is NULL
            )
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('type', $orderedToolType);
        $query->setParameter('bundles', $this->bundles);

        return $query->getResult();
    }

    /**
     * Returns the visible tools in a workspace.
     *
     * @param Workspace $workspace
     *
     * @return array[Tool]
     */
    public function findDisplayedToolsByWorkspace(
        Workspace $workspace,
        $orderedToolType = 0
    ) {
        $dql = '
            SELECT tool
            FROM Claroline\CoreBundle\Entity\Tool\Tool tool
            LEFT JOIN tool.plugin p
            JOIN tool.orderedTools ot
            JOIN ot.workspace ws
            WHERE ws = :workspace
            AND ot.type = :type
            AND (
                CONCAT(p.vendorName, p.bundleName) IN (:bundles)
                OR tool.plugin is NULL
            )
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);
        $query->setParameter('type', $orderedToolType);
        $query->setParameter('bundles', $this->bundles);

        return $query->getResult();
    }

    /**
     * Returns the number of tools visible in a workspace.
     *
     * @param Workspace $workspace
     *
     * @return int
     */
    public function countDisplayedToolsByWorkspace(
        Workspace $workspace,
        $orderedToolType = 0
    ) {
        $dql = "
            SELECT count(tool)
            FROM Claroline\CoreBundle\Entity\Tool\Tool tool
            JOIN tool.orderedTools ot
            JOIN ot.workspace ws
            LEFT JOIN tool.plugin p
            WHERE ws.id = {$workspace->getId()}
            AND ot.type = :type
            AND (
                CONCAT(p.vendorName, p.bundleName) IN (:bundles)
                OR tool.plugin is NULL
            )
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('type', $orderedToolType);
        $query->setParameter('bundles', $this->bundles);

        return $query->getSingleScalarResult();
    }

    public function findToolsDispayableInWorkspace()
    {
        $dql = '
            SELECT t
            FROM Claroline\CoreBundle\Entity\Tool\Tool t
            LEFT JOIN t.plugin p
            WHERE t.isDisplayableInWorkspace = true
            AND (
                CONCAT(p.vendorName, p.bundleName) IN (:bundles)
                OR t.plugin is NULL
            )
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('bundles', $this->bundles);

        return $query->getResult();
    }

    /**
     * @return \Claroline\CoreBundle\Entity\Tool\Tool|]
     */
    public function findAllWithPlugin()
    {
        return $this->createQueryBuilder('tool')
            ->leftJoin('tool.plugin', 'p')
            ->where('CONCAT(p.vendorName, p.bundleName) IN (:bundles)')
            ->orWhere('tool.plugin is null')
            ->getQuery()
            ->setParameter('bundles', $this->bundles)
            ->getResult();
    }
}
