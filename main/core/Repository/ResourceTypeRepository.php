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

class ResourceTypeRepository extends EntityRepository
{
    /**
     * Returns all the resource types introduced by plugins.
     *
     * @return array[ResourceType]
     */
    public function findPluginResourceTypes()
    {
        $dql = '
            SELECT rt FROM Claroline\CoreBundle\Entity\Resource\ResourceType rt
            WHERE rt.plugin IS NOT NULL
        ';
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    /**
     * Returns the number of existing resources for each resource type.
     *
     * @param null $workspace
     *
     * @return array
     */
    public function countResourcesByType($workspace = null)
    {
        $qb = $this
            ->createQueryBuilder('type')
            ->select('type.id, type.name, COUNT(rs.id) AS total')
            ->leftJoin('Claroline\CoreBundle\Entity\Resource\ResourceNode', 'rs', 'WITH', 'type = rs.resourceType')
            ->andWhere('type.name != :directoryName')
            ->setParameter('directoryName', 'directory')
            ->groupBy('type.id')
            ->orderBy('total', 'DESC');

        if (!empty($workspace)) {
            $qb->leftJoin('Claroline\CoreBundle\Entity\Workspace\Workspace', 'ws', 'WITH', 'ws = rs.workspace')
                ->andWhere('ws = :workspace')
                ->setParameter('workspace', $workspace);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Returns all the resource types introduced by plugins.
     *
     * @return array[ResourceType]
     */
    public function findAll()
    {
        $dql = '
          SELECT rt, ma FROM Claroline\CoreBundle\Entity\Resource\ResourceType rt
          LEFT JOIN rt.actions ma';

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    /**
     * @param array $excludedTypeNames
     *
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
}
