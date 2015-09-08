<?php

namespace FormaLibre\PresenceBundle\Manager;

use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Manager\RoleManager;
use FormaLibre\PresenceBundle\Entity\PresenceRights;

/**
 *@DI\Service("formalibre.manager.presence_manager")
 * 
 */
class PresenceManager
{
    
    private $om;
    private $rightsRepo;
    private $roleManager;
     
    /**
     * @DI\InjectParams({
     *      "om"                 = @DI\Inject("claroline.persistence.object_manager"),
     *      "roleManager"        = @DI\Inject("claroline.manager.role_manager")
     * })
     * 
     */        
    public function __construct(ObjectManager $om, RoleManager $roleManager) {
        
        $this->om =$om;
        $this->rightsRepo=$om->getRepository("FormaLibrePresenceBundle:PresenceRights");
        $this->roleManager =$roleManager;
        
    }
    
    public function getAllPresenceRights(){
        
        $toflush=false;
        $allRights=$this->rightsRepo->findAll();
        $rolesPlateforme=$this->roleManager->getAllPlatformRoles();
        
        $existentRights=array();
        foreach ($allRights as $oneRight){
            $role=$oneRight->getRole();
            $existentRights[$role->getId()]=$oneRight; 
        }
        foreach ($rolesPlateforme as $oneRolePlateforme){
            
            if(!isset($existentRights[$oneRolePlateforme->getId()])){
                
                $toflush=true;
                
                $newRight=new PresenceRights();
                $newRight->setRole($oneRolePlateforme);
                $newRight->setMask(PresenceRights::PERSONAL_ARCHIVES);
                $this->om->persist($newRight);
                
                $existentRights[$oneRolePlateforme->getId()]=$newRight;
            }
        }
        if($toflush){
        $this->om->flush();
        }
        
        return $existentRights;        
    }
    
    
}