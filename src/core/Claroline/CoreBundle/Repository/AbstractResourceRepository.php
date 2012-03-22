<?php

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

class AbstractResourceRepository extends NestedTreeRepository
{   /*
    public function getNavigableChildren(AbstractResource $rsrc = null)
    {
        $resourceType = $this->_em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneBy(array('type' => 'directory'));
        
          $dql="SELECT r FROM Claroline\CoreBundle\Entity\Resource\AbstractResource r 
                 JOIN r.resourceType rt 
                 WHERE rt.id = '{$resourceType->getId()}'
                 AND r.parent = '{$rsrc->getId()}'
             ";
        
        $query = $this->_em->createQuery($dql);
        return $query->getResult();
    } */
    
    public function getUserRootResource($user)
    {
        $dql = "
            SELECT r FROM Claroline\CoreBundle\Entity\Resource\AbstractResource r
            WHERE r.lvl = 0
            AND r.user = '{$user->getId()}'";
            
            $query = $this->_em->createQuery($dql);
            
            return $query->getResult();       
    }
    
    public function getWorkspaceRootResource($workspace)
    {
        $dql = "
            SELECT r FROM Claroline\CoreBundle\Entity\Resource\AbstractResource r 
            WHERE r.parent = 0
            AND r.workspace = '{$workspace->getId()}'";
            
            $query = $this->_em->createQuery($dql);
            return $query->getResult();           
    }
}