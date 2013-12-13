<?php

namespace Innova\PathBundle\Manager;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Manager\ResourceManager;

use Innova\PathBundle\Entity\Path;
use Innova\PathBundle\Entity\Step;
use Innova\PathBundle\Entity\Step2ResourceNode;
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
     * Current entity manage for data persist
     * @var \Doctrine\ORM\EntityManagerEntity Manager $em
     */
    protected $em;

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
     * @param EntityManager $entityManager
     * @param SecurityContext $securityContext
     */
    public function __construct(EntityManager $entityManager, SecurityContext $securityContext, ResourceManager $resourceManager)
    {
        $this->em = $entityManager;
        $this->resourceManager = $resourceManager;
        $this->security = $securityContext;
        
        // Retrieve current user
        $this->user = $this->security->getToken()->getUser();
    }
    
    /**
     * Create a new path
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceNode
     */
    public function create()
    {
        $nonDigitalResource = new NonDigitalResource();
        $nonDigitalResource->setName();
        $nonDigitalResource->setDescription();
        $this->em->persist($nonDigitalResource);
        $this->em->flush();

        $resourceType = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneByName("non_digital_resource");
        $workspace = $this->em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->findOneById();
        $parent = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findWorkspaceRoot($workspace);
        
        $nonDigitalResource = $this->resourceManager->create($newPath, $resourceType, $this->user, $workspace, $parent, null);
        
        return $nonDigitalResource->getResourceNode();
    }
}