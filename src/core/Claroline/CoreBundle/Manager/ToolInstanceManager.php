<?php

namespace Claroline\CoreBundle\Manager;

use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Security\RightManager\RightManager;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\ToolInstance;
use Claroline\CoreBundle\Entity\Tool;

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

    public function create(Tool $tool, AbstractWorkspace $workspace)
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

    public function delete(ToolInstance $toolInstance, AbstractWorkspace $workspace)
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
