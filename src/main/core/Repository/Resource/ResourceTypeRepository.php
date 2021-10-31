<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Repository\Resource;

use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\PluginManager;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ResourceTypeRepository extends ServiceEntityRepository
{
    /** @var array */
    private $bundles;

    public function __construct(ManagerRegistry $registry, PluginManager $pluginManager)
    {
        $this->bundles = $pluginManager->getEnabled();

        parent::__construct($registry, ResourceType::class);
    }

    /**
     * Returns all the resource types introduced by plugins.
     *
     * @return ResourceType[]
     */
    public function findPluginResourceTypes()
    {
        $dql = '
            SELECT rt FROM Claroline\CoreBundle\Entity\Resource\ResourceType rt
            JOIN rt.plugin p
            WHERE CONCAT(p.vendorName, p.bundleName) IN (:bundles)
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('bundles', $this->bundles);

        return $query->getResult();
    }

    /**
     * Returns the number of existing resources for each resource type.
     *
     * @param Workspace $workspace
     * @param null      $organizations
     *
     * @return ResourceType[]
     */
    public function countResourcesByType($workspace = null, $organizations = null)
    {
        $qb = $this
            ->createQueryBuilder('rType')
            ->select('rType.id, rType.name, COUNT(rs.id) AS total')
            ->leftJoin('Claroline\CoreBundle\Entity\Resource\ResourceNode', 'rs', 'WITH', 'rType = rs.resourceType')
            ->andWhere('rType.name != :directoryName')
            ->andWhere('rs.active = 1')
            ->setParameter('directoryName', 'directory')
            ->groupBy('rType.id')
            ->orderBy('total', 'DESC');

        if (!empty($workspace)) {
            $qb->leftJoin('Claroline\CoreBundle\Entity\Workspace\Workspace', 'ws', 'WITH', 'ws = rs.workspace')
                ->andWhere('ws = :workspace')
                ->setParameter('workspace', $workspace);
        }

        if (!empty($organizations)) {
            $qb->leftJoin('Claroline\CoreBundle\Entity\Workspace\Workspace', 'ws', 'WITH', 'ws = rs.workspace')
                ->join('ws.organizations', 'orgas')
                ->andWhere('orgas IN (:organizations)')
                ->setParameter('organizations', $organizations);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Returns all the resource types introduced by plugins.
     *
     * @param bool $filterEnabled - when true, it will only return resource types for enabled plugins
     *
     * @return ResourceType[]
     */
    public function findAll($filterEnabled = true)
    {
        if (!$filterEnabled) {
            return parent::findAll();
        }

        $dql = '
          SELECT rt FROM Claroline\CoreBundle\Entity\Resource\ResourceType rt
          LEFT JOIN rt.plugin p
          WHERE (CONCAT(p.vendorName, p.bundleName) IN (:bundles)
          OR rt.plugin is NULL)
          AND rt.isEnabled = true';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('bundles', $this->bundles);

        return $query->getResult();
    }

    /**
     * @return array
     */
    public function findTypeNamesNotIn(array $excludedTypeNames)
    {
        $dql = '
            SELECT t.name FROM Claroline\CoreBundle\Entity\Resource\ResourceType t
            WHERE t.name NOT IN (:types)
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('types', $excludedTypeNames);

        return $query->getResult();
    }

    public function findAllTypeNames()
    {
        $dql = '
            SELECT t.name AS name FROM Claroline\CoreBundle\Entity\Resource\ResourceType t';
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    /**
     * Returns enabled resource types by their names.
     *
     * @return ResourceType[]
     */
    public function findByNames(array $names, bool $enabled = true)
    {
        if (count($names) > 0) {
            $dql = '
                SELECT r
                FROM Claroline\CoreBundle\Entity\Resource\ResourceType r
                WHERE r.name IN (:names) 
            ';

            if ($enabled) {
                $dql .= ' AND r.isEnabled = true';
            }

            $query = $this->_em->createQuery($dql);
            $query->setParameter('names', $names);
            $result = $query->getResult();
        } else {
            $result = [];
        }

        return $result;
    }
}
