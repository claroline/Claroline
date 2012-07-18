<?php

namespace Claroline\CoreBundle\Library\Resource;

use Symfony\Component\Form\FormFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Resource\ResourceInstance;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;

class Manager
{

    /** @var EntityManager */
    private $em;

    /** @var FormFactory */
    private $formFactory;

    /** @var ContainerInterface */
    protected $container;

    /**
     * Constructor
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->em = $container->get('doctrine.orm.entity_manager');
        $this->formFactory = $container->get('form.factory');
        $this->container = $container;
    }

    /**
     * Creates a resource. If instanceParentId is null, added to the root.
     *
     * @param integer          $parentInstanceId
     * @param integer          $workspaceId
     * @param AbstractResource $object
     * @param boolean          $instance the return type
     *
     * @return ResourceInstance | Resource
     *
     * @throws \Exception
     */
    public function create(AbstractResource $resource, $parentInstanceId, $resourceType, $returnInstance = true)
    {
        $resourceType = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneBy(array('type' => $resourceType));
        $user = $this->container->get('security.context')->getToken()->getUser();

        if (null !== $resource) {
            $ri = new ResourceInstance();
            $ri->setCreator($user);
            $dir = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceInstance')->find($parentInstanceId);
            $ri->setParent($dir);
            $resource->setResourceType($resourceType);
            $ri->setWorkspace($dir->getWorkspace());
            $ri->setResource($resource);
            $this->em->persist($ri);
            $resource->setCreator($user);
            $this->em->persist($resource);
            $this->em->flush();

            return $returnInstance ? $ri : $resource;
        }

        throw \Exception("failed to create resource");
    }

    /**
     * Moves a resource instance
     *
     * @param ResourceInstance  $child
     * @param ResourceInstance  $parent
     */
    public function move(ResourceInstance $child, ResourceInstance $parent)
    {
        $child->setWorkspace($parent->getWorkspace());
        $child->setParent($parent);
        $this->em->flush();
    }
}
