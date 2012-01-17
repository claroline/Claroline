<?php

namespace Claroline\WorkspaceBundle\Manager;

use Claroline\WorkspaceBundle\Entity\Workspace;
use Claroline\PluginBundle\Entity\ToolInstance;
use Claroline\PluginBundle\Entity\Tool;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Claroline\SecurityBundle\Manager\RightManager\RightManager;

class ToolInstanceManager
{
    /**
     * @var EntityManager
     */
    protected $em;
    
    /** 
    * @var RightManager
     */
    protected $rightManager;

    /**
     * @var EntityRepository
     */
    protected $repository;

    /**
     * @var string
     */
    protected $class;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     * @param RightManager $rm
     * @param string $class
     */
    public function __construct(EntityManager $em, RightManager $rm, $class)
    {
        $this->em = $em;
        $this->repository = $em->getRepository($class);
        $this->rightManager = $rm;
        $this->class = $em->getClassMetadata($class)->name;

    }

    public function create(Tool $tool, Workspace $workspace)
    {  
        $toolInstance = new ToolInstance();
        $toolInstance->setToolType($tool);
        $toolInstance->setHostWorkspace($workspace);
        $this->em->persist($toolInstance);
        
        $workspace->addToolInstance($toolInstance);
        $this->em->persist($workspace);
        $this->em->flush();
        
        return $toolInstance;
    }

    public function delete(ToolInstance $toolInstance, Workspace $workspace)
    {       
        $workspace->removeToolInstance($toolInstance);
        $this->em->remove($toolInstance);
        $this->em->flush();
    }
    
    public function setPermission($toolInstance, $user, $mask)
    {
        $this->rightManager->addRight($toolInstance, $user, $mask);
    }
}
