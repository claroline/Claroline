<?php

namespace Claroline\CoreBundle\Library\Manager;

use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Library\Security\RightManager\RightManagerInterface;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Symfony\Component\Form\FormFactory;
use Claroline\CoreBundle\Entity\User;

class ResourceManager
{
    /** @var Doctrine\ORM\EntityManager */
    protected $em;
    
    /** @var RightManagerInterface */
    protected $rightManager;
    
    /** @var string */
    protected $controller;    

    public function __construct(FormFactory $formFactory, EntityManager $em, RightManagerInterface $rightManager)
    {
        $this->em = $em;
        $this->rightManager = $rightManager;
        $this->formFactory=$formFactory;
    }
    
    public function createResource(AbstractResource $resource, User $owner)
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
        $resources = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource')->findBy(array('user' => $user->getId()));
        
        return $resources;        
    }
    
    public function findAll()
    {
        $resources = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource')->findAll();
        
        return $resources; 
    }
    
    public function getChildren($resource)
    {
        $resources = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource')->children($resource, true);
                
        return $resources;    
    }
    
    public function getChildrenById($id)
    {
        $resource = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource')->find($id);
        $resources = $this->getChildren($resource);
        
        return $resources;
    }
    
    public function getRootResourcesOfUser($user)
    {
        $resources = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource')->getUserRootResource($user);
        
        return $resources;
    }
      
    public function find($id)
    { 
        $resource = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource')->find($id);
        
        return $resource;
    }
}