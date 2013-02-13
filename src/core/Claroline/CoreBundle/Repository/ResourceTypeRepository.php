<?php

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ResourceTypeRepository extends EntityRepository
{
    public function findPluginResourceTypes()
    {
        $dql = "
            SELECT rt FROM Claroline\CoreBundle\Entity\Resource\ResourceType rt
            WHERE rt.plugin IS NOT NULL
        ";

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function findPluginResourceNameFqcns()
    {
        $sql = 'SELECT class FROM claro_resource_type WHERE plugin_id IS NOT NULL';

        return $this->_em
            ->getConnection()
            ->query($sql)
            ->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function findByIds(array $resourceTypeIds)
    {
        $dql = '
            SELECT rt
            FROM Claroline\CoreBundle\Entity\Resource\ResourceType rt
            WHERE rt.id IN (' . implode(',', $resourceTypeIds) . ')
        ';

        return $this->_em->createQuery($dql)
            ->getResult();
    }
}