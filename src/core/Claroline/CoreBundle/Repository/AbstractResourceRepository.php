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
    
    /*
     * 3 differents type of roots
     * 1) they don't have parent and are in the current repository
     * 2) they are copied by copy and only have 1 instance + their parent are in a different repository
     * 3) they are copied by reference, have strictly more than 1 instance and their parent are in a different repository
     * => this is quite a problem because it's a N to N relation, wich mean that once there is at least 2 instances, 
     * there is always some family in somewhere else
     */
    public function getRepositoryListableRootResource($repository)
    {   
         $dql = "
            SELECT r FROM Claroline\CoreBundle\Entity\Resource\AbstractResource r
            JOIN r.repositories repo
            LEFT JOIN r.parent par
            LEFT JOIN par.repositories repoPar
            WHERE r.lvl = 0
            AND repo.id = {$repository->getId()}
            OR
            repo.id = {$repository->getId()}
            AND repoPar.id != {$repository->getId()}
            AND r.amountInstance = 1
            OR 
            repo.id = {$repository->getId()}
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
            AND repo.id =  {$repository->getId()}
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