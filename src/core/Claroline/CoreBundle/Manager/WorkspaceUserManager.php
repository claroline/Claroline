<?php

namespace Claroline\CoreBundle\Manager;

use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Security\RightManager\RightManager;
use Claroline\CoreBundle\Entity\Workspace;
use Claroline\CoreBundle\Entity\User;

class WorkspaceUserManager
{
    private $entityManager;
    private $rightManager;
    
    public function __construct(EntityManager $em, RightManager $rm)
    {
        $this->entityManager = $em;
        $this->rightManager = $rm;
    }
    
    public function addUser(Workspace $workspace, User $user, $rightMask = MaskBuilder::MASK_VIEW)
    {
        $workspace->addUser($user);
        $this->entityManager->persist($workspace);
        $this->entityManager->flush($workspace);
        $this->rightManager->addRight($workspace, $user, $rightMask);
    }
    
    public function removeUser(Workspace $workspace, User $user)
    {
        $workspace->removeUser($user);
        $this->entityManager->persist($workspace);
        $this->entityManager->flush($workspace);
        $this->rightManager->removeAllRights($workspace, $user);
    }
}