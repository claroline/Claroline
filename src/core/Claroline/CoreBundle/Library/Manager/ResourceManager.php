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
    
    public function __construct(FormFactory $formFactory, EntityManager $em, RightManagerInterface $rightManager)
    {
        $this->em = $em;
        $this->rightManager = $rightManager;
        $this->formFactory=$formFactory;
    }
    
    public function createResource(AbstractResource $resource, User $owner)
    {
        //$resource->setUser($owner);
        $this->em->persist($resource);
        $this->em->flush();
        $this->rightManager->addRight($resource, $owner, MaskBuilder::MASK_OWNER);
    }
}