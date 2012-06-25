<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

class ResourceInstanceRepository extends NestedTreeRepository
{
    public function getWSListableRootResource($ws)
    {
        $dql = "
            SELECT re FROM Claroline\CoreBundle\Entity\Resource\ResourceInstance re
            WHERE re.lvl = 0
            AND re.workspace = {$ws->getId()}
        ";

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function getDirectoryDirectChildren($ri)
    {
        $dql = "
            SELECT ri FROM Claroline\CoreBundle\Entity\Resource\ResourceInstance ri
            JOIN ri.resourceType rt
            WHERE rt.type = 'directory'
            AND ri.parent = {$ri->getId()}
        ";

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function getNotDirectoryDirectChildren($ri)
    {
        $dql = "
            SELECT ri FROM Claroline\CoreBundle\Entity\Resource\ResourceInstance ri
            JOIN ri.resourceType rt
            WHERE rt.type != 'directory'
            AND ri.parent = {$ri->getId()}
        ";

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function getListableChildren($resourceInstance)
    {
        $dql = "
            SELECT ri FROM Claroline\CoreBundle\Entity\Resource\ResourceInstance ri
            JOIN ri.parent par
            JOIN ri.abstractResource res
            WHERE par.id = {$resourceInstance->getId()}
            AND res.resourceType IN
            (
                SELECT rt FROM Claroline\CoreBundle\Entity\Resource\ResourceType rt
                WHERE rt.isListable = 1
            )
        ";

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }
}