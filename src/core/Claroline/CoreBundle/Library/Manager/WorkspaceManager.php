<?php

namespace Claroline\CoreBundle\Library\Manager;

use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Library\Security\RightManager\RightManager;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace;
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
    
    public function createWorkspace($baseName, User $manager)
    {
        $workspace = new SimpleWorkspace();
        $workspace->setName($baseName);
        
        $this->entityManager->persist($workspace);
        $this->entityManager->flush();
        
        $workspace->initBaseRoles();
        
        $manager->addRole($workspace->getManagerRole());
        $this->entityManager->flush();
        
        $this->rightManager->addRight($workspace, $manager, MaskBuilder::MASK_OWNER);
        
        return $workspace;
    }
    
    public function addCollaborator(AbstractWorkspace $workspace, User $user)
    {
        $user->addRole($workspace->getCollaboratorRole());
        
        $this->entityManager->flush();
    }
}