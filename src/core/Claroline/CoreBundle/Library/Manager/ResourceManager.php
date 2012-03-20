<?php

namespace Claroline\CoreBundle\Library\Manager;

use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Library\Security\RightManager\RightManagerInterface;
use Claroline\CoreBundle\Entity\Resource;
use Claroline\CoreBundle\Entity\User;
//use Claroline\CoreBundle\Library\Resource\ResourceInterface;

class ResourceManager
{
    /** @var Doctrine\ORM\EntityManager */
    protected $em;
    
    /** @var RightManagerInterface */
    protected $rightManager;
    
    /** @var string */
    protected $controller;    

    public function __construct(EntityManager $em, RightManagerInterface $rightManager)
    {
        $this->em = $em;
        $this->rightManager = $rightManager;
    }
    
    public function createResource(Resource $resource, User $owner)
    {
        $resource->setUser($owner);
        $this->em->persist($resource);
        $this->em->flush();
        $this->rightManager->addRight($resource, $owner, MaskBuilder::MASK_OWNER);
    }
    
    public function getControllerName()
    {
        return $this->controller;
    }

    public function getResourcesOfUser($user)
    {
        $resources = $this->em->getRepository('ClarolineCoreBundle:Resource')->findBy(array('user' => $user->getId()));
        
        return $resources;        
    }
    
    public function findAll()
    {
        $resources = $this->em->getRepository('ClarolineCoreBundle:Resource')->findAll();
        
        return $resources; 
    }
}