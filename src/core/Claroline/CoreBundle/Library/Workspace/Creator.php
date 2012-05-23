<?php

namespace Claroline\CoreBundle\Library\Workspace;

use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Library\Security\RightManager\RightManager;
use Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Resource\Repository;

class Creator
{
    private $entityManager;
    private $rightManager;
    
    public function __construct(EntityManager $em, RightManager $rm)
    {
        $this->entityManager = $em;
        $this->rightManager = $rm;
    }
    
    public function createWorkspace(Configuration $config, User $manager = null)
    {
        $config->check();
        
        $workspaceType = $config->getWorkspaceType();
        $workspace = new $workspaceType;
        $workspace->setName($config->getWorkspaceName());
        $workspace->setPublic($config->isPublic());
        $workspace->setType($config->getType());    
        $this->entityManager->persist($workspace);
        $this->entityManager->flush();   
        $workspace->initBaseRoles(); 
        $workspace->getVisitorRole()->setTranslationKey($config->getVisitorTranslationKey());
        $workspace->getCollaboratorRole()->setTranslationKey($config->getCollaboratorTranslationKey());
        $workspace->getManagerRole()->setTranslationKey($config->getManagerTranslationKey());
        $this->entityManager->flush();
        
        if (null !== $manager)
        {
            $manager->addRole($workspace->getManagerRole());
            $this->rightManager->addRight($workspace, $manager, MaskBuilder::MASK_OWNER);
        }
        
        $this->entityManager->flush();
        
        return $workspace;
    }
}