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
            SELECT r FROM Claroline\CoreBundle\Entity\Resource\Directory r
            JOIN r.resourcesInstance ri
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
            SELECT r FROM Claroline\CoreBundle\Entity\Resource\Directory r
            JOIN r.resourcesInstance ri
            JOIN ri.resourceType rt
            WHERE rt.type != 'directory'
            AND ri.parent = {$ri->getId()}
        ";
            
        $query = $this->_em->createQuery($dql);
            
        return $query->getResult();   
    }
    
}