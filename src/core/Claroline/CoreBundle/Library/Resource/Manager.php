<?php

namespace Claroline\CoreBundle\Library\Resource;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManager;
use Gedmo\Exception\UnexpectedValueException;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\ResourceRights;
use Claroline\CoreBundle\Library\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Library\Event\CopyResourceEvent;
use Claroline\CoreBundle\Library\Event\ResourceLogEvent;

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
     * Creates a resource. If the user is null, the creator of the resource is
     * the user currently logged in.
     *
     * @param AbstractResource  $resource       The resource to be created
     * @param integer           $parentId       The id of the parent resource
     * @param string            $resourceType   The string identifier of the resource type
     * @param User              $user           The creator of the resource (optional)
     *
     * @return  AbstractResource
     *
     * @throws \Exception
     */
    public function create(AbstractResource $resource, $parentId, $resourceType, User $user = null)
    {
        $resourceType = $this->em
            ->getRepository('ClarolineCoreBundle:Resource\ResourceType')
            ->findOneBy(array('name' => $resourceType));

        if ($user === null) {
            $user = $this->sc->getToken()->getUser();
        }

        $resource->setCreator($user);
        $parent = $this->em
            ->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
            ->find($parentId);

        $resource->setParent($parent);
        $resource->setResourceType($resourceType);
        $resource->setWorkspace($parent->getWorkspace());
        $rename = $this->ut->getUniqueName($resource, $parent);
        $resource->setName($rename);
        $resource->setCreator($user);

        if ($resource->getUserIcon() === null) {
            $resource = $this->ic->setResourceIcon($resource);
        } else {
            //upload the icon
            $iconFile = $resource->getUserIcon();
            $icon = $this->ic->createCustomIcon($iconFile);
            $this->em->persist($icon);
            $resource->setIcon($icon);
        }

        $this->em->persist($resource);
        $this->setResourceRights($parent, $resource);
        $event = new ResourceLogEvent($resource, ResourceLogEvent::CREATE_ACTION);
        $this->ed->dispatch('log_resource', $event);

        return $resource;
    }

    /**
     * Moves a resource.
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
        $rights = $child->getRights();

        foreach ($rights as $right) {
            $this->em->remove($right);
        }
        try {
            $this->em->flush();
            $this->setResourceRights($parent, $child);

            $event = new ResourceLogEvent(
                $child,
                ResourceLogEvent::MOVE_ACTION
            );
            $this->ed->dispatch('log_resource', $event);

            return $child;
        } catch (UnexpectedValueException $e) {
            throw new \UnexpectedValueException("You cannot move a directory into itself");
        }
    }

    /**
     * Removes a resource.
     *
     * @param AbstractResource $resource
     */
    public function delete(AbstractResource $resource)
    {
        if (get_class($resource) == 'Claroline\CoreBundle\Entity\Resource\ResourceShortcut') {
            $logEvent = new ResourceLogEvent($resource, ResourceLogEvent::DELETE_ACTION);
            $this->ed->dispatch('log_resource', $logEvent);
            $this->em->remove($resource);
        } else {
            if ($resource->getResourceType()->getName() !== 'directory') {
                $eventName = $this->ut->normalizeEventName(
                    'delete', $resource->getResourceType()->getName()
                );

                $event = new DeleteResourceEvent($resource);
                $this->ed->dispatch($eventName, $event);
                $logEvent = new ResourceLogEvent($resource, ResourceLogEvent::DELETE_ACTION);
                $this->ed->dispatch('log_resource', $logEvent);

            } else {
                $this->deleteDirectory($resource);
            }
        }

        $this->em->flush();
    }

    /**
     * Copies a resource in a directory.
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
            $this->setResourceRights($parent, $copy);

            if ($resource->getResourceType()->getName() == 'directory') {
                foreach ($resource->getChildren() as $child) {
                    $this->copy($child, $copy);
                }
            }

            $logevent = new ResourceLogEvent(
                $resource,
                ResourceLogEvent::COPY_ACTION
            );

            $this->ed->dispatch('log_resource', $logevent);
        }

        $this->em->persist($copy);
        $this->em->flush();

        return $copy;
    }

    /**
     * Copies the resource rights from $parent to $children.
     *
     * @param AbstractResource $parent
     * @param AbstractResource $children
     *
     */
    public function setResourceRights(AbstractResource $parent, AbstractResource $children)
    {
        $resourceRights = $this->em
            ->getRepository('ClarolineCoreBundle:Resource\ResourceRights')
            ->findBy(array('resource' => $parent));
        foreach ($resourceRights as $resourceRight) {
            $rc = new ResourceRights();
            $rc->setRole($resourceRight->getRole());
            $rc->setResource($children);
            $rc->setRightsFrom($resourceRight);
            $rc->setWorkspace($resourceRight->getWorkspace());

            if ($children->getResourceType()->getName() === 'directory') {
                $rc->setCreatableResourceTypes($resourceRight->getCreatableResourceTypes()->toArray());
            }

            $this->em->persist($rc);
        }

        $children->setOwnerRights($parent->getOwnerRights());

        $this->em->persist($children);
        $this->em->flush();
    }

    /**
     * Creates a resource copy with no name.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\AbstractResource $originalResource
     *
     * @return AbstractResource
     */
    private function createCopy(AbstractResource $originalResource)
    {
        $user = $this->sc->getToken()->getUser();

        if ($originalResource->getResourceType()->getName() === 'directory') {
            $resourceCopy = new Directory();
            $dirType = $this->em
                ->getRepository('ClarolineCoreBundle:Resource\ResourceType')
                ->findOneByName('directory');
            $resourceCopy->setResourceType($dirType);
        } else {
            $event = new CopyResourceEvent($originalResource);
            $eventName = $this->ut->normalizeEventName('copy', $originalResource->getResourceType()->getName());
            $this->ed->dispatch($eventName, $event);
            $resourceCopy = $event->getCopy();
            if ($resourceCopy === null) {
                throw new \Exception(
                    "The resource {$originalResource->getResourceType()->getName()}" .
                    " couldn't be created."
                );
            }
            $resourceCopy->setResourceType($originalResource->getResourceType());
        }

        $resourceCopy->setCreator($user);
        $resourceCopy->setIcon($originalResource->getIcon());
        $this->em->persist($resourceCopy);

        return $resourceCopy;
    }

    /**
     * Removes a directory.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\AbstractResource $resource
     *
     * @throws \LogicException
     */
    private function deleteDirectory(AbstractResource $resource)
    {
        if ($resource->getParent() === null) {
            throw new \LogicException('Root directory cannot be removed');
        }

        $children = $this->em
            ->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
            ->getChildren($resource, false);
        foreach ($children as $child) {
            $event = new DeleteResourceEvent($child);
            $this->ed->dispatch("delete_{$child->getResourceType()->getName()}", $event);
            $logEvent = new ResourceLogEvent($child, ResourceLogEvent::DELETE_ACTION);
            $this->ed->dispatch('log_resource', $logEvent);
            $this->em->flush();
        }

        $this->em->remove($resource);
        $logEvent = new ResourceLogEvent($resource, ResourceLogEvent::DELETE_ACTION);
        $this->ed->dispatch('log_resource', $logEvent);
    }
}
