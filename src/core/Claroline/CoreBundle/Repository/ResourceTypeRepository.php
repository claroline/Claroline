<?php

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
     * Returns the fully qualified class name of the resource introduced by plugins.
     *
     * @return array
     */
    public function findPluginResourceNameFqcns()
    {
        $sql = 'SELECT class FROM claro_resource_type WHERE plugin_id IS NOT NULL';

        return $this->_em
            ->getConnection()
            ->query($sql)
            ->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Returns the number of existing resources for each resource type.
     *
     * @return array
     */
    public function countResourcesByType()
    {
        $qb = $this
            ->createQueryBuilder('type')
            ->select('type.id, type.name, COUNT(rs.id) AS total')
            ->leftJoin('Claroline\CoreBundle\Entity\Resource\AbstractResource', 'rs', 'WITH', 'type = rs.resourceType')
            ->groupBy('type.id')
            ->orderBy('total', 'DESC');

        return $qb->getQuery()->getResult();
    }
}
