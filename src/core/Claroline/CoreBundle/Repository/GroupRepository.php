<?php

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;

class GroupRepository extends EntityRepository
{
    public function getGroupsOfWorkspace(AbstractWorkspace $workspace)
    {
       $dql ="
            SELECT g FROM Claroline\CoreBundle\Entity\Group g  
            JOIN g.workspaceRoles wr JOIN wr.workspace w WHERE w.id = '{$workspace->getId()}'"
       ;   
        $query = $this->_em->createQuery($dql);
        
        return $query->getResult();   
    }
    
    public function getLazyUnregisteredGroupsOfWorkspace(AbstractWorkspace $workspace, $numberIteration, $groupAmount)
    {
       $offset=$numberIteration*$groupAmount;

       $dql="SELECT g FROM Claroline\CoreBundle\Entity\Group g WHERE g NOT IN 
            (
                 SELECT gr FROM Claroline\CoreBundle\Entity\Group gr
                 JOIN gr.workspaceRoles wr JOIN wr.workspace w WHERE w.id = '{$workspace->getId()}'
             )";

       $query = $this->_em->createQuery($dql);
       $query->setMaxResults($groupAmount);
       $query->setFirstResult($offset);
        
       return $query->getResult();       
    }
    
    public function getUnregisteredGroupsOfWorkspaceFromGenericSearch($search, AbstractWorkspace $workspace)
    {
        $search = strtoupper($search);
        
        $dql = "
            SELECT g FROM Claroline\CoreBundle\Entity\Group g 
            WHERE UPPER(g.name) LIKE '%".$search."%'
            AND g NOT IN (SELECT gr FROM Claroline\CoreBundle\Entity\Group gr
            JOIN gr.workspaceRoles wr JOIN wr.workspace w WHERE w.id = '{$workspace->getId()}')    
        "; 
            
        $query = $this->_em->createQuery($dql);
        $query->setMaxResults(200);
        
        return $query->getResult(); 
    }
}