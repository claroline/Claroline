<?php

namespace Claroline\CoreBundle\Manager;

use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Security\RightManager\RightManager;
use Claroline\CoreBundle\Entity\Workspace;
use Claroline\CoreBundle\Entity\WorkspaceRole;
use Claroline\CoreBundle\Entity\User;

class WorkspaceManager
{
    private $entityManager;
    private $rightManager;
    
    public function __construct(EntityManager $em, RightManager $rm)
    {
        $this->entityManager = $em;
        $this->rightManager = $rm;
    }
    
    public function createWorkspace($baseName, User $manager, array $publicNames = null)
    {
        $managerRole = new WorkspaceRole();
        $managerRole->setName("ROLE_{$baseName} manager");
        $manager->addRole($managerRole);
        
        $workspace = new Workspace();
        $workspace->setName($baseName);
        $workspace->addRole($managerRole);
        
        $managerRole->setWorkspace($workspace);
        $this->entityManager->persist($managerRole);
        
        if ($publicNames === null)
        {
            $simpleUserRole = new WorkspaceRole();
            $simpleUserRole->setName("ROLE_{$baseName} user");
            $simpleUserRole->setWorkspace($workspace);
            $this->entityManager->persist($simpleUserRole);
            
            $workspace->addRole($simpleUserRole);
        }
        else
        {
            foreach ($publicNames as $publicName)
            {
                $publicRole = new WorkspaceRole();
                $publicRole->setName("ROLE_{$publicName} user");
                $publicRole->setWorkspace($workspace);
                $this->entityManager->persist($publicRole);
            
                $workspace->addRole($publicRole);
            }
        }
        
        $this->entityManager->persist($workspace);
        $this->entityManager->flush();
        $this->rightManager->addRight($workspace, $manager, MaskBuilder::MASK_OWNER);
        
        return $workspace;
    }
    
    public function addSimpleUser(Workspace $workspace, User $user)
    {
        foreach ($workspace->getRoles() as $role)
        {
            if ($role->getName() == "ROLE_{$workspace->getName()} user")
            {
                $user->addRole($role);
                $this->entityManager->flush();
                break;
            }
        }
    }
}