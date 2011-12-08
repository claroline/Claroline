<?php

namespace Claroline\ResourceBundle\Manager;

use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Doctrine\ORM\EntityManager;
use Claroline\SecurityBundle\Service\RightManager\RightManagerInterface;
use Claroline\ResourceBundle\Entity\Resource;
use Claroline\UserBundle\Entity\User;

class ResourceManager
{
    /** @var Doctrine\ORM\EntityManager */
    private $em;
    
    /** @var RightManagerInterface */
    private $rightManager;

    public function __construct(EntityManager $em, RightManagerInterface $rightManager)
    {
        $this->em = $em;
        $this->rightManager = $rightManager;
    }
    
    public function createResource(Resource $resource, User $owner)
    {
        $this->em->persist($resource);
        $this->em->flush();
        $this->rightManager->addRight($resource, $owner, MaskBuilder::MASK_OWNER);
    }
}