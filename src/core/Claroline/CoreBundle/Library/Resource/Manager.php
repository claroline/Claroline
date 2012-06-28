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
     * Creates a resource
     *
     * @param integer          $instanceParentId
     * @param integer          $workspaceId
     * @param AbstractResource $object
     * @param boolean          $instance the return type
     *
     * @return ResourceInstance | Resource
     *
     * @throws \Exception
     */
    public function create(AbstractResource $object, $workspaceId, $instanceParentId = null,  $returnInstance = false)
    {
        $class = get_class($object);
        $resourceType = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneBy(array('class' => $class));
        $user = $this->container->get('security.context')->getToken()->getUser();
        $name = $this->findResService($resourceType);
        $resServ = $this->container->get($name);
        $resource = $resServ->add($object, $instanceParentId, $user);

        if (null !== $resource) {
            $ri = new ResourceInstance();
            $ri->setCreator($user);
            $dir = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceInstance')->find($instanceParentId);
            $ri->setParent($dir);
            $resource->setResourceType($resourceType);
            $ri->setCopy(0);
            $workspace = $this->em->getRepository('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace')->find($workspaceId);
            $ri->setWorkspace($workspace);
            $ri->setResource($resource);
            $this->em->persist($ri);
            $resource->setCreator($user);
            $this->em->flush();
            $this->container->get('claroline.security.right_manager')->addRight($ri, $user, MaskBuilder::MASK_OWNER);
            $roles = $workspace->getWorkspaceRoles();

            //default rights: will be changed.
            foreach ($roles as $role)
            {
                $this->container->get('claroline.security.right_manager')->addRight($ri, $role, MaskBuilder::MASK_VIEW);
            }

            return $returnInstance ? $ri : $resource;
        }

        throw \Exception("failed to create resource");
    }

    /**
     * Moves a resource instance
     *
     * @param ResourceInstance  $child
     * @param AbstractWorkspace $workspace
     * @param ResourceInstance  $parent
     */
    public function move(ResourceInstance $child, AbstractWorkspace $workspace, ResourceInstance $parent = null)
    {
        $child->setWorkspace($workspace);
        $child->setParent($parent);
        $this->em->flush();

        return 0;
    }

    /**
     * Returns the service's name for the ResourceType $resourceType
     *
     * @param ResourceType $resourceType
     *
     * @return string
     */
    public function findResService(ResourceType $resourceType)
    {
        $services = $this->container->getParameter('claroline.resource_controllers');
        $names = array_keys($services);

        foreach ($names as $name) {
            $type = $this->container->get($name)->getResourceType();

            if ($type == $resourceType->getType()) {
                return $name;
            }
        }

        return null;
    }

}
