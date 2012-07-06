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
     * @param integer          $instanceParentId
     * @param integer          $workspaceId
     * @param AbstractResource $object
     * @param boolean          $instance the return type
     *
     * @return ResourceInstance | Resource
     *
     * @throws \Exception
     */
    public function create(AbstractResource $object, $instanceParentId, $returnInstance = false)
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
            $ri->setWorkspace($dir->getWorkspace());
            $ri->setResource($resource);
            $this->em->persist($ri);
            $resource->setCreator($user);
            $this->em->flush();
            $this->container->get('claroline.security.right_manager')->addRight($ri, $user, MaskBuilder::MASK_OWNER);
            $roles = $dir->getWorkspace()->getWorkspaceRoles();
            $masks = \Claroline\CoreBundle\Library\Security\SymfonySecurity::getSfMasks();
            $keys = array_keys($masks);

            foreach ($roles as $role) {
                $mask = $role->getResMask();
                foreach ($keys as $key) {
                    if ($mask & $key) {
                        $this->container->get('claroline.security.right_manager')->addRight($ri, $role, $key);
                    }
                }
            }

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

    /**
     * Adds a permission to a user on an instance
     *
     * @param integer $instanceId
     * @param integer $userId
     * @param integer $maskId
     */
    public function addInstanceRight($instanceId, $userId, $maskId)
    {
        $resourceInstance = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->find($instanceId);
        $user = $this->em->getRepository('ClarolineCoreBundle:User')->find($userId);
        $this->container->get('claroline.security.right_manager')->addRight($resourceInstance, $user, intval($maskId));
    }

    /**
     * Removes a permission from a user on an instance
     *
     * @param integer $instanceId
     * @param integer $userId
     * @param integer $maskId
     */
    public function removeInstanceRight($instanceId, $userId, $maskId)
    {
        $resourceInstance = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->find($instanceId);
        $user = $this->em->getRepository('ClarolineCoreBundle:User')->find($userId);
        $this->container->get('claroline.security.right_manager')->removeRight($resourceInstance, $user, intval($maskId));
    }

    /**
     * Adds a permission to a workspace role on the workspace resources instance
     *
     * @param integer $roleId
     * @param integer $maskId
     */
    public function addResourceRolePermission($roleId, $maskId)
    {
        $role = $this->em->getRepository('ClarolineCoreBundle:WorkspaceRole')->find($roleId);
        $workspace = $role->getWorkspace();
        $instances = $workspace->getResourcesInstance();

         $role->addResourceMask(intval($maskId));
            foreach ($instances as $instance) {
                $this->container->get('claroline.security.right_manager')->addRight($instance, $role, intval($maskId));
                foreach ($this->em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->children($instance, true) as $children) {
                    $this->container->get('claroline.security.right_manager')->addRight($children, $role, intval($maskId));
                }
            }
         $this->em->flush();
    }

    /**
     * Removes a permission from a workspace role on the workspace resources instance
     *
     * @param integer $roleId
     * @param integer $maskId
     */
   public function removeResourceRolePermission($roleId, $maskId)
   {
        $role = $this->em->getRepository('ClarolineCoreBundle:WorkspaceRole')->find($roleId);
        $workspace = $role->getWorkspace();
        $instances = $workspace->getResourcesInstance();
        $role->removeResourceMask(intval($maskId));

        foreach ($instances as $instance) {
            $this->container->get('claroline.security.right_manager')->removeRight($instance, $role, intval($maskId));
            foreach ($this->em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->children($instance, true) as $children) {
                $this->container->get('claroline.security.right_manager')->removeRight($children, $role, intval($maskId));
            }
        }
        $this->em->flush();
   }
}
