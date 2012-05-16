<?php

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

class AbstractResourceRepository extends NestedTreeRepository
{
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
    } 
        
    public function getUserRootResource($user)
    {
        $dql = "
            SELECT r FROM Claroline\CoreBundle\Entity\Resource\AbstractResource r
            WHERE r.lvl = 0
            AND r.user = '{$user->getId()}'";
            
            $query = $this->_em->createQuery($dql);
            
            return $query->getResult();       
    }
    
    public function getUserListableRootResource($user)
    {
        $dql = "
            SELECT r FROM Claroline\CoreBundle\Entity\Resource\AbstractResource r
            WHERE r.lvl = 0
            AND r.user = '{$user->getId()}'
            AND r.resourceType
            IN (SELECT rt FROM Claroline\CoreBundle\Entity\Resource\ResourceType rt
                WHERE rt.isListable = 1)"
            ;
            
            $query = $this->_em->createQuery($dql);
            
            return $query->getResult(); 
    }
    
    public function getRepositoryListableOriginalRootResource($repository)
    {
        $dql = "
            SELECT r FROM Claroline\CoreBundle\Entity\Resource\AbstractResource r
            JOIN r.repositories repo WHERE repo.id = {$repository->getId()}
            AND r.resourceType
            IN (SELECT rt FROM Claroline\CoreBundle\Entity\Resource\ResourceType rt
                WHERE rt.isListable = 1)
            AND r.copy = 0
            AND r.lvl = 0
            ";
             
            $query = $this->_em->createQuery($dql);
            
            return $query->getResult(); 
    }
    
    //Dans un repository, une racine est une resource dont le père ne fait pas partie du repository en question 
    //ou dont le père est null.
    //Comme le père peut être null, il faut utiliser LEFT JOIN
    public function getRepositoryListableRootResource($repository)
    {
        $dql = "
            SELECT r FROM Claroline\CoreBundle\Entity\Resource\AbstractResource r
            JOIN r.repositories repo
            LEFT JOIN r.parent par
            LEFT JOIN par.repositories repoPar
            WHERE
            r.lvl = 0
            and repo.id = {$repository->getId()}
            OR
            repo.id = {$repository->getId()}
            AND r.resourceType
            IN (SELECT rt FROM Claroline\CoreBundle\Entity\Resource\ResourceType rt
                WHERE rt.isListable = 1)
            AND repoPar.id != {$repository->getId()}
            ";
            
            $query = $this->_em->createQuery($dql);
            
            return $query->getResult(); 
    }
    
    public function getRepositoryListableChildren($repository, $resource)
    {
        $dql = "
            SELECT r FROM Claroline\CoreBundle\Entity\Resource\AbstractResource r
            JOIN r.repositories repo 
            JOIN r.parent par 
            WHERE par.id = {$resource->getId()}
            AND repo.id = {$repository->getId()}
            AND r.resourceType
            IN (SELECT rt FROM Claroline\CoreBundle\Entity\Resource\ResourceType rt
                WHERE rt.isListable = 1)
            ";
            
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