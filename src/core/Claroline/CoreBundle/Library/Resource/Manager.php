<?php

namespace Claroline\CoreBundle\Library\Resource;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManager;
use Gedmo\Exception\UnexpectedValueException  ;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Resource\ResourceInstance;
use Claroline\CoreBundle\Entity\Resource\IconType;
use Claroline\CoreBundle\Entity\Resource\ResourceIcon;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Library\Resource\Event\DeleteResourceEvent;

class Manager
{
    /** @var EntityManager */
    private $em;
    /** @var ContainerInterface */
    protected $container;
    /** @var EventDispatcher */
    private $ed;
    /** @var SecurityContext */
    private $sc;
    /** @var Utilities */
    private $ut;
    /** @var IconCreator */
    private $ic;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->em = $container->get('doctrine.orm.entity_manager');
        $this->ed = $container->get('event_dispatcher');
        $this->sc = $container->get('security.context');
        $this->ut = $container->get('claroline.resource.utilities');
        $this->ic = $container->get('claroline.resource.icon_creator');
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
    public function create(AbstractResource $resource, $parentInstanceId, $resourceType, $returnInstance = true, $mimeType = null, $user = null)
    {
        $resourceType = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneBy(array('type' => $resourceType));

        if($user == null){
            $user = $this->sc->getToken()->getUser();
        }

        if (null !== $resource) {
            $ri = new ResourceInstance();
            $ri->setCreator($user);
            $dir = $this->em
                ->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')
                ->find($parentInstanceId);
            $ri->setParent($dir);
            $resource->setResourceType($resourceType);
            $ri->setWorkspace($dir->getWorkspace());
            $ri->setResource($resource);
            $ri->setName($resource->getName());
            $rename = $this->ut->getUniqueName($ri, $dir);
            $ri->setName($rename);
            $this->em->persist($ri);
            $resource->setCreator($user);
            $this->em->persist($resource);
            $resource = $this->ic->setResourceIcon($resource, $mimeType);
            $this->em->flush();

            return $returnInstance ? $ri : $resource;
        }

        throw \Exception("failed to create resource");
    }

    /**
     * Moves a resource instance.
     *
     * @param ResourceInstance  $child
     * @param ResourceInstance  $parent
     */
    public function move(ResourceInstance $child, ResourceInstance $parent)
    {
        $child->setWorkspace($parent->getWorkspace());
        $child->setParent($parent);
        $rename = $this->ut->getUniqueName($child, $parent);
        $child->setName($rename);
        try {
            $this->em->flush();

            return $child;
        } catch (UnexpectedValueException $e) {
            throw new \UnexpectedValueException("You cannot move a directory into itself");
        }
    }

    /**
     * Removes a resource instance.
     *
     * @param ResourceInstance $resourceInstance
     */
    public function delete(ResourceInstance $resourceInstance)
    {
        if (1 === $resourceInstance->getResource()->getInstanceCount()) {

            if ($resourceInstance->getResourceType()->getType() !== 'directory') {
                $eventName = $this->ut->normalizeEventName(
                    'delete', $resourceInstance->getResourceType()->getType()
                );
                $event = new DeleteResourceEvent(array($resourceInstance->getResource()));
                $this->ed->dispatch($eventName, $event);
            } else {
                $this->deleteDirectory($resourceInstance);
            }
        }

        $resourceInstance->getResource()->removeResourceInstance($resourceInstance);
        $this->em->remove($resourceInstance);
        $this->em->flush();
    }

    /**
     * Adds a resource to a directory by reference.
     *
     * @param ResourceInstance $resourceInstance
     * @param ResourceInstance $parent
     */
    public function addToDirectoryByReference(ResourceInstance $resourceInstance, ResourceInstance $parent)
    {
        $resource = $resourceInstance->getResource();

        if ($resource->getResourceType()->getType() != 'directory') {
            $instanceCopy = $this->createReference($resource);
            $instanceCopy->setParent($parent);
            $instanceCopy->setWorkspace($parent->getWorkspace());
            $rename = $this->ut->getUniqueName($resourceInstance, $parent);
            $instanceCopy->setName($rename);
        } else {
            $instances = $resource->getResourceInstances();
            $instanceCopy = $this->createCopy($instances[0]);
            $instanceCopy->setParent($parent);
            $instanceCopy->setWorkspace($parent->getWorkspace());
            $rename = $this->ut->getUniqueName($resourceInstance, $parent);
            $instanceCopy->setName($rename);

            foreach ($instances[0]->getChildren() as $child) {
                $this->addToDirectoryByReference($child, $instanceCopy);
            }
        }

        $this->em->persist($instanceCopy);

        return $instanceCopy;
    }

    private function createCopy(ResourceInstance $resourceInstance)
    {
        $user = $this->sc->getToken()->getUser();
        $ric = new ResourceInstance();
        $ric->setCreator($user);
        $this->em->flush();

        if ($resourceInstance->getResourceType()->getType()=='directory') {
            $resourceCopy = new Directory();
            $resourceCopy->setName($resourceInstance->getResource()->getName());
            $resourceCopy->setCreator($user);
            $resourceCopy->setResourceType($this->em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')->findOneByType('directory'));
            $resourceCopy->addResourceInstance($ric);
            $resourceCopy->setIcon($resourceInstance->getResource()->getIcon());
        } else {
            $event = new CopyResourceEvent($resourceInstance->getResource());
            $eventName = $this->ut->normalizeEventName('copy', $resourceInstance->getResourceType()->getType());
            $this->ed->dispatch($eventName, $event);
            $resourceCopy = $event->getCopy();
            $resourceCopy->setCreator($user);
            $resourceCopy->setResourceType($resourceInstance->getResourceType());
            $resourceCopy->addResourceInstance($ric);
        }

        $this->em->persist($resourceCopy);
        $ric->setResource($resourceCopy);

        return $ric;
    }

    private function createReference(AbstractResource $resource)
    {
        $ric = new ResourceInstance();
        $ric->setCreator($this->sc->getToken()->getUser());
        $ric->setResource($resource);
        $resource->addResourceInstance($ric);

        return $ric;
    }

    private function deleteDirectory(ResourceInstance $directoryInstance)
    {
        if ($directoryInstance->getParent() === null){
           throw new \LogicException('Root directory cannot be removed');
        }

        $children = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->getChildren($directoryInstance, false);
        foreach ($children as $child) {
            $rsrc = $child->getResource();
            if ($rsrc->getInstanceCount() === 1) {
                if ($child->getResourceType()->getType() == 'directory') {
                   $this->em->remove($rsrc);
                   $this->em->flush();
                } else {
                    $event = new DeleteResourceEvent(array($child->getResource()));
                    $this->ed->dispatch("delete_{$child->getResourceType()->getType()}", $event);
                    $this->em->flush();
                }
            }
        }

        $this->em->remove($directoryInstance->getResource());
    }
}
