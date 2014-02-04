<?php

namespace Innova\PathBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\SecurityContext;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Manager\ResourceManager;
use Innova\PathBundle\Entity\NonDigitalResource;

/**
 * NonDigitalResource Manager
 * Manages life cycle of NonDigitalResource
 * @author Innovalangues <contact@innovalangues.net>
 *
 */
class NonDigitalResourceManager
{
    /**
     * Current object manager for data persist
     * @var \Doctrine\Common\Persistence\ObjectManager $om
     */
    protected $om;

    /**
     * claro resource manager
     * @var \Claroline\CoreBundle\Manager\ResourceManager
     */
    protected $resourceManager;
    
    /**
     * Current security context
     * @var \Symfony\Component\Security\Core\SecurityContext $security
     */
    protected $security;
    
    /**
     * Authenticated user
     * @var \Claroline\CoreBundle\Entity\User\User $user
     */
    protected $user;
    
    /**
     * Class constructor - Inject required services
     * @param \Doctrine\Common\Persistence\ObjectManager       $objectManager
     * @param \Symfony\Component\Security\Core\SecurityContext $securityContext
     * @param \Claroline\CoreBundle\Manager\ResourceManager    $resourceManager
     */
    public function __construct(
        ObjectManager   $objectManager, 
        SecurityContext $securityContext, 
        ResourceManager $resourceManager)
    {
        $this->om              = $objectManager;
        $this->resourceManager = $resourceManager;
        $this->security        = $securityContext;
        
        // Retrieve current user
        $this->user = $this->security->getToken()->getUser();
    }
    
    public function create(AbstractWorkspace $workspace, $name)
    {
        $nonDigitalResource = new NonDigitalResource();
        $nonDigitalResource->setName($name);
        $this->om->persist($nonDigitalResource);

        $resourceType = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneByName("innova_non_digital_resource");
        $parent = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findWorkspaceRoot($workspace);
        
        $nonDigitalResource = $this->resourceManager->create($nonDigitalResource, $resourceType, $this->user, $workspace, $parent, null);
        
        $this->om->flush();
        
        return $nonDigitalResource;
    }

    public function edit(AbstractWorkspace $workspace, $resourceId, $name, $description, $type)
    {
        if ($resourceId == null)
        {
            $nonDigitalResource = $this->create($workspace, $name);
            $resourceNode = $nonDigitalResource->getResourceNode();
        }
        else
        {
            $resourceNode = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findOneById($resourceId);
            $nonDigitalResource = $this->om->getRepository('InnovaPathBundle:NonDigitalResource')->findOneByResourceNode($resourceNode);
        }

        $nonDigitalResource->setName($name);
        $nonDigitalResource->setDescription($description);
        $nonDigitalResource->setResourceNode($resourceNode);
        $nonDigitalResource->setNonDigitalResourceType($this->om->getRepository('InnovaPathBundle:NonDigitalResourceType')->findOneByName($type));
        $resourceNode->setName($name);

        $this->om->persist($nonDigitalResource);
        $this->om->persist($resourceNode);
        $this->om->flush();

        return $nonDigitalResource;
    }
    
}