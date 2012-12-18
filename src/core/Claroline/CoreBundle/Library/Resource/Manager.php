<?php

namespace Claroline\CoreBundle\Library\Resource;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManager;
use Gedmo\Exception\UnexpectedValueException  ;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Library\Resource\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Library\Resource\Event\CopyResourceEvent;
use Claroline\CoreBundle\Library\Logger\Event\ResourceLoggerEvent;

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
     * @return  Abstractesource
     *
     * @throws \Exception
     */
    public function create(AbstractResource $resource, $parentId, $resourceType, $mimeType = null, $user = null)
    {
        $resourceType = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneBy(array('name' => $resourceType));

        if($user == null){
            $user = $this->sc->getToken()->getUser();
        }

        if (null !== $resource) {

            $resource->setCreator($user);
            $parent = $this->em
                ->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource')
                ->find($parentId);

            $resource->setParent($parent);
            $resource->setResourceType($resourceType);
            $resource->setWorkspace($parent->getWorkspace());
            $rename = $this->ut->getUniqueName($resource, $parent);
            $resource->setName($rename);
            $resource->setCreator($user);

            if ($resource->getUserIcon() == null){
                $resource = $this->ic->setResourceIcon($resource, $mimeType);

            } else {
                //upload the icon
                $iconFile = $resource->getUserIcon();
                $icon = $this->ic->createCustomIcon($iconFile);
                $this->em->persist($icon);
                $resource->setIcon($icon);
            }

            $this->em->persist($resource);
            $this->em->flush();


            $event = new ResourceLoggerEvent(
                $resource,
                ResourceLoggerEvent::CREATE_ACTION
            );
            $this->ed->dispatch('log_resource', $event);

            return $resource;
        }

        throw \Exception("failed to create resource");
    }

    /**
     * Moves a resource instance.
     *
     * @param Abstractesource  $child
     * @param Abstractesource  $parent
     */
    public function move(AbstractResource $child, AbstractResource $parent)
    {
        $child->setWorkspace($parent->getWorkspace());
        $child->setParent($parent);
        $rename = $this->ut->getUniqueName($child, $parent);
        $child->setName($rename);
        try {
            $this->em->flush();
            $event = new ResourceLoggerEvent(
                $child,
                ResourceLoggerEvent::MOVE_ACTION
            );
            $this->ed->dispatch('log_resource', $event);

            return $child;
        } catch (UnexpectedValueException $e) {
            throw new \UnexpectedValueException("You cannot move a directory into itself");
        }
    }

    /**
     * Removes a resource instance.
     *
     * @param AbstractResource $resource
     */
    public function delete(AbstractResource $resource)
    {
        //If it's a link, the link is removed.
        if (get_class($resource) == 'Claroline\CoreBundle\Entity\Resource\ResourceShortcut') {
            $this->em->remove($resource);
        } else {
            if ($resource->getResourceType()->getName() !== 'directory') {
                $eventName = $this->ut->normalizeEventName(
                    'delete', $resource->getResourceType()->getName()
                );

                $event = new DeleteResourceEvent($resource);
                $this->ed->dispatch($eventName, $event);
            } else {
                $this->deleteDirectory($resource);
            }
        }
        $this->em->flush();
    }

    /**
     * Copy a resource in a directory
     *
     * @param AbstractResource $resource
     * @param AbstractResource $parent
     */
    public function copy(AbstractResource $resource, AbstractResource $parent)
    {
        if (get_class($resource) == 'Claroline\CoreBundle\Entity\Resource\ResourceShortcut') {
            $copy = new \Claroline\CoreBundle\Entity\Resource\ResourceShortcut();
            $copy->setParent($parent);
            $copy->setWorkspace($parent->getWorkspace());
            $copy->setResource($resource->getResource());
            $copy->setIcon($resource->getIcon());
            $copy->setResourceType($resource->getResourceType());
            $copy->setCreator($this->sc->getToken()->getUser());
            $copy->setName($resource->getName());
            $rename = $this->ut->getUniqueName($resource, $parent);
            $copy->setName($rename);
        } else {
            $copy = $this->createCopy($resource);
            $copy->setParent($parent);
            $copy->setWorkspace($parent->getWorkspace());
            $copy->setName($resource->getName());
            $copy->setName($this->ut->getUniqueName($copy, $parent));

            if ($resource->getResourceType()->getName() == 'directory') {
                foreach ($resource->getChildren() as $child) {
                    $this->copy($child, $copy);
                }
            }

            $logevent = new ResourceLoggerEvent(
                $resource,
                ResourceLoggerEvent::COPY_ACTION
            );

            $this->ed->dispatch('log_resource', $logevent);
        }

        $this->em->persist($copy);
        $this->em->flush();

        return $copy;
    }

    /**
     *  Creates a resource copy with no name
     *
     * @param \Claroline\CoreBundle\Entity\Resource\AbstractResource $originalResource
     *
     * @return AbstractResource
     */
    private function createCopy(AbstractResource $originalResource)
    {
        $user = $this->sc->getToken()->getUser();

        if ($originalResource->getResourceType()->getName()=='directory') {
            $resourceCopy = new Directory();
            $resourceCopy->setResourceType($this->em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')->findOneByName('directory'));
        } else {
            $event = new CopyResourceEvent($originalResource);
            $eventName = $this->ut->normalizeEventName('copy', $originalResource->getResourceType()->getName());
            $this->ed->dispatch($eventName, $event);
            $resourceCopy = $event->getCopy();

            $resourceCopy->setResourceType($originalResource->getResourceType());
        }

        $resourceCopy->setCreator($user);
        $resourceCopy->setIcon($originalResource->getIcon());
        $this->em->persist($resourceCopy);

        return $resourceCopy;
    }

    private function deleteDirectory(AbstractResource $resource)
    {
        if ($resource->getParent() === null){
           throw new \LogicException('Root directory cannot be removed');
        }

        $children = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource')->getChildren($resource, false);
        foreach ($children as $child) {
            $event = new DeleteResourceEvent($child);
            $this->ed->dispatch("delete_{$child->getResourceType()->getName()}", $event);
            $this->em->flush();
        }

        $this->em->remove($resource);
    }
}
