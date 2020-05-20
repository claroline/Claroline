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

use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\PluginManager;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class OrderedToolRepository extends ServiceEntityRepository
{
    /** @var array */
    private $bundles;

    /**
     * OrderedToolRepository constructor.
     *
     * @param RegistryInterface $registry
     * @param PluginManager     $manager
     */
    public function __construct(RegistryInterface $registry, PluginManager $manager)
    {
        $this->bundles = $manager->getEnabled(true);

        parent::__construct($registry, OrderedTool::class);
    }

    public function findByName($name)
    {
        return $this->_em
            ->createQuery('
                SELECT ot
                FROM Claroline\CoreBundle\Entity\Tool\OrderedTool ot
                JOIN ot.tool t
                WHERE t.name = :name
                ORDER BY ot.order
            ')
            ->setParameter('name', $name)
            ->getResult();
    }

    /**
     * @param string         $name
     * @param Workspace|null $workspace
     *
     * @return OrderedTool
     */
    public function findOneByNameAndWorkspace($name, Workspace $workspace = null)
    {
        return $this->_em
            ->createQuery('
                SELECT ot
                FROM Claroline\CoreBundle\Entity\Tool\OrderedTool ot
                JOIN ot.tool t
                WHERE ot.workspace = :workspace
                AND t.name = :name
                ORDER BY ot.order
            ')
            ->setParameter('workspace', $workspace)
            ->setParameter('name', $name)
            ->getOneOrNullResult();
    }

    /**
     * Returns all the workspace ordered tools.
     *
     * @param Workspace $workspace
     *
     * @return OrderedTool[]
     */
    public function findByWorkspace(Workspace $workspace)
    {
        return $this->_em
            ->createQuery('
                SELECT ot
                FROM Claroline\CoreBundle\Entity\Tool\OrderedTool AS ot
                JOIN ot.tool AS t
                LEFT JOIN t.plugin AS p
                WHERE ot.workspace = :workspace
                AND (
                    CONCAT(p.vendorName, p.bundleName) IN (:bundles)
                    OR t.plugin is NULL
                )
                ORDER BY ot.order ASC
            ')
            ->setParameter('workspace', $workspace)
            ->setParameter('bundles', $this->bundles)
            ->getResult();
    }

    /**
     * Returns the workspace ordered tools accessible to some given roles.
     *
     * @param Workspace $workspace
     * @param array     $roles
     *
     * @return OrderedTool[]
     */
    public function findByWorkspaceAndRoles(Workspace $workspace, array $roles)
    {
        if (0 === count($roles)) {
            return [];
        }

        return $this->_em
            ->createQuery('
                SELECT ot
                FROM Claroline\CoreBundle\Entity\Tool\OrderedTool AS ot
                JOIN ot.tool AS t
                LEFT JOIN t.plugin AS p
                JOIN ot.rights AS r
                JOIN r.role AS rr
                WHERE ot.workspace = :workspace
                AND rr.name IN (:roleNames)
                AND BIT_AND(r.mask, 1) = 1
                AND (
                    CONCAT(p.vendorName, p.bundleName) IN (:bundles)
                    OR t.plugin is NULL
                )
                ORDER BY ot.order ASC
            ')
            ->setParameter('workspace', $workspace)
            ->setParameter('roleNames', $roles)
            ->setParameter('bundles', $this->bundles)
            ->getResult();
    }

    /**
     * @param string $name
     *
     * @return OrderedTool
     */
    public function findOneByNameAndDesktop($name)
    {
        return $this->_em
            ->createQuery('
                SELECT ot
                FROM Claroline\CoreBundle\Entity\Tool\OrderedTool ot
                JOIN ot.tool t
                WHERE ot.workspace IS NULL
                AND ot.user IS NULL
                AND t.name = :name
                ORDER BY ot.order
            ')
            ->setParameter('name', $name)
            ->getOneOrNullResult();
    }

    public function findByDesktop()
    {
        return $this->_em
            ->createQuery('
                SELECT ot
                FROM Claroline\CoreBundle\Entity\Tool\OrderedTool AS ot
                JOIN ot.tool AS t
                LEFT JOIN t.plugin AS p
                WHERE ot.workspace IS NULL
                AND ot.user IS NULL
                AND (
                    CONCAT(p.vendorName, p.bundleName) IN (:bundles)
                    OR t.plugin is NULL
                )
                ORDER BY ot.order
            ')
            ->setParameter('bundles', $this->bundles)
            ->getResult();
    }

    public function findByDesktopAndRoles(array $roles)
    {
        if (0 === count($roles)) {
            return [];
        }

        return $this->_em
            ->createQuery('
                SELECT ot
                FROM Claroline\CoreBundle\Entity\Tool\OrderedTool AS ot
                JOIN ot.tool AS t
                LEFT JOIN t.plugin AS p
                JOIN ot.rights AS r
                JOIN r.role AS rr
                WHERE ot.workspace IS NULL
                AND ot.user IS NULL
                AND rr.name IN (:roleNames)
                AND BIT_AND(r.mask, 1) = 1
                AND (
                    CONCAT(p.vendorName, p.bundleName) IN (:bundles)
                    OR t.plugin is NULL
                )
                ORDER BY ot.order
            ')
            ->setParameter('roleNames', $roles)
            ->setParameter('bundles', $this->bundles)
            ->getResult();
    }
}
