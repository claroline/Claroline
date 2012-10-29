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

    public function findVisiblePluginResourceType()
    {
        $dql = "
            SELECT rt FROM Claroline\CoreBundle\Entity\Resource\ResourceType rt
            WHERE rt.plugin IS NOT NULL AND rt.isVisible = 1
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

    public function findBrowsableResourceType($isDirectoryIncluded = true)
    {
        $dql = "
            SELECT rt FROM Claroline\CoreBundle\Entity\Resource\ResourceType rt
            WHERE rt.isBrowsable = true";
        if (!$isDirectoryIncluded) {
            $dql.="and rt.type != 'directory'";
        }

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }
}