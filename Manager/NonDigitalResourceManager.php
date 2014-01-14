<?php

namespace Innova\PathBundle\Manager;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
    


    public function create(AbstractWorkspace $workspace, $name)
    {
        $nonDigitalResource = new NonDigitalResource();
        $nonDigitalResource->setName($name);
        $this->em->persist($nonDigitalResource);

        $resourceType = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneByName("non_digital_resource");
        $parent = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findWorkspaceRoot($workspace);
        
        $nonDigitalResource = $this->resourceManager->create($nonDigitalResource, $resourceType, $this->user, $workspace, $parent, null);

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
            $resourceNode = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findOneById($resourceId);
            $nonDigitalResource = $this->em->getRepository('InnovaPathBundle:NonDigitalResource')->findOneByResourceNode($resourceNode);
        }

        $nonDigitalResource->setName($name);
        $nonDigitalResource->setDescription($description);
        $nonDigitalResource->setResourceNode($resourceNode);
        $nonDigitalResource->setNonDigitalResourceType($this->em->getRepository('InnovaPathBundle:NonDigitalResourceType')->findOneByName($type));
        $resourceNode->setName($name);

        $this->em->persist($nonDigitalResource);
        $this->em->persist($resourceNode);
        $this->em->flush();

        return $nonDigitalResource;
    }
    
}