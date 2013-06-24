<?php

namespace Claroline\CoreBundle\Library\Resource;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManager;
use Gedmo\Exception\UnexpectedValueException;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\ResourceShortcut;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Library\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Library\Event\CopyResourceEvent;
use Claroline\CoreBundle\Library\Event\LogResourceMoveEvent;
use Claroline\CoreBundle\Library\Event\LogResourceDeleteEvent;
use Claroline\CoreBundle\Library\Event\LogResourceCopyEvent;
use Claroline\CoreBundle\Repository\AbstractResourceRepository;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.resource.manager")
 */
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
    /** @var ResourceManager */
    private $ut;
    /** @var IconCreator */
    private $ic;
    /** @var AbstractResourceRepository */
    private $resourceRepo;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     *
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container")
     * })
     */
    public function __construct(ContainerInterface $container)
    {
        $this->em = $container->get('doctrine.orm.entity_manager');
        $this->ed = $container->get('event_dispatcher');
        $this->sc = $container->get('security.context');
        $this->ut = $container->get('claroline.manager.resource_manager');
        $this->ic = $container->get('claroline.manager.icon_manager');
        $this->resourceRepo = $this->em->getRepository('ClarolineCoreBundle:Resource\AbstractResource');
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
     * @param array             $rights         And array of rights
     *
     * @return  AbstractResource
     *
     * @throws \Exception
     */

    /**
     * Moves a resource.
     *
     * @param Abstractesource  $child
     * @param Abstractesource  $parent
     */
    public function move(AbstractResource $child, AbstractResource $parent)
    {
        $this->removePosition($child);
        $this->setLastPosition($parent, $child);
        $oldParent = $child->getParent();
        $child->setParent($parent);
        $rename = $this->ut->getUniqueName($child, $parent);
        $child->setName($rename);
        $this->em->persist($child);

        try {
            $this->em->flush();
            $log = new LogResourceMoveEvent($child, $oldParent);
            $this->ed->dispatch('log', $log);

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
        $this->removePosition($resource);

        if (get_class($resource) == 'Claroline\CoreBundle\Entity\Resource\ResourceShortcut') {
            // $logEvent = new ResourceLogEvent($resource, ResourceLogEvent::DELETE_ACTION);
            // $this->ed->dispatch('log_resource', $logEvent);
            $this->em->remove($resource);
        } else {
            if ($resource->getResourceType()->getName() !== 'directory') {
                $eventName = 'delete_'.$resource->getResourceType()->getName();
                $event = new DeleteResourceEvent($resource);
                $this->ed->dispatch($eventName, $event);

                $log = new LogResourceDeleteEvent($resource);
                $this->ed->dispatch('log', $log);
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
    public function copy(AbstractResource $resource, AbstractResource $parent, User $user)
    {
        if (get_class($resource) == 'Claroline\CoreBundle\Entity\Resource\ResourceShortcut') {
            $copy = new \Claroline\CoreBundle\Entity\Resource\ResourceShortcut();
            $copy->setParent($parent);
            $copy->setWorkspace($parent->getWorkspace());
            $copy->setResource($resource->getResource());
            $copy->setIcon($resource->getIcon());
            $copy->setResourceType($resource->getResourceType());
            $copy->setCreator($user);
            $copy->setName($resource->getName());
            $rename = $this->ut->getUniqueName($resource, $parent);
            $copy->setName($rename);
        } else {
            $copy = $this->createCopy($resource, $user);
            $copy->setParent($parent);
            $copy->setWorkspace($parent->getWorkspace());
            $copy->setName($resource->getName());
            $copy->setName($this->ut->getUniqueName($copy, $parent));
            $this->cloneRights($resource, $copy);
            $this->em->flush();

            if ($resource->getResourceType()->getName() == 'directory') {
                foreach ($resource->getChildren() as $child) {
                    $this->copy($child, $copy, $user);
                }
            }

            $log = new LogResourceCopyEvent($copy, $resource);
            $this->ed->dispatch('log', $log);
        }

        $this->setLastPosition($parent, $copy);
        $this->em->persist($copy);
        $this->em->flush();

        return $copy;
    }

    /**
     * Creates a resource copy with no name.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\AbstractResource $originalResource
     *
     * @return AbstractResource
     */
    private function createCopy(AbstractResource $originalResource, User $user)
    {
        if ($originalResource->getResourceType()->getName() === 'directory') {
            $resourceCopy = new Directory();
            $dirType = $this->em
                ->getRepository('ClarolineCoreBundle:Resource\ResourceType')
                ->findOneByName('directory');
            $resourceCopy->setResourceType($dirType);
        } else {
            $event = new CopyResourceEvent($originalResource);
            $eventName = 'copy_' . $originalResource->getResourceType()->getName();
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
        $logger = $this->container->get('logger');
        if ($resource->getParent() === null) {
            throw new \LogicException('Root directory cannot be removed');
        }

        $children = $this->em
            ->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
            ->getChildren($resource, false, 'path', 'DESC');

        $logger->info('Try to delete '.$resource->getName());
        foreach ($children as $child) {
            $logger->info('Try to delete child '.$child->getName());
            $event = new DeleteResourceEvent($child);
            $this->ed->dispatch("delete_{$child->getResourceType()->getName()}", $event);

            $logChild = new LogResourceDeleteEvent($child);
            $this->ed->dispatch('log', $logChild);
        }

        $this->em->remove($resource);
        $this->em->flush();

        $log = new LogResourceDeleteEvent($resource);
        $this->ed->dispatch('log', $log);
    }

    /**
     * Builds an array used by the query builder from the query parameters.
     * @see filterAction from ResourceController
     *
     * @param array $queryParameters
     *
     * @return array
     */
    public function buildSearchArray($queryParameters)
    {
        $allowedStringCriteria = array('name', 'dateFrom', 'dateTo');
        $allowedArrayCriteria = array('roots', 'types');
        $criteria = array();

        foreach ($queryParameters as $parameter => $value) {
            if (in_array($parameter, $allowedStringCriteria) && is_string($value)) {
                $criteria[$parameter] = $value;
            } elseif (in_array($parameter, $allowedArrayCriteria) && is_array($value)) {
                $criteria[$parameter] = $value;
            }
        }

        return $criteria;
    }

    /**
     * Checks if a path is valid.
     * If strict = false, the path may not exists in the database (every ancestor must be a directory).
     *
     * @param array $ancestors
     * @param boolean $strict
     *
     * @return boolean
     */
    public function isPathValid(array $ancestors, $strict = true)
    {
        if (!$strict) {
            array_pop($ancestors);
            foreach ($ancestors as $ancestor) {
                if ($ancestor->getResourceType()->getName() !== 'directory') {
                    return false;
                }
            }
            return true;
        }

        $continue = true;

        for ($i = 0, $size = count($ancestors); $i < $size; $i++) {

            if (isset($ancestors[$i + 1])) {
                if ($ancestors[$i + 1]->getParent() == $ancestors[$i]) {
                    $continue = true;
                } else {
                    if ($this->hasLinkTo($ancestors[$i], $ancestors[$i + 1])) {
                        $continue = true;
                    } else {
                        $continue = false;
                    }
                }
            }

            if (!$continue) {

                return false;
            }
        }

        return true;
    }

    private function hasLinkTo(Directory $parent, Directory $target)
    {
        $shortcuts = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceShortcut')
            ->findBy(array('parent' => $parent));

        foreach ($shortcuts as $shortcut) {
            if ($shortcut->getResource() == $target) {
                return true;
            }
        }

        return false;
    }

    /**
     * Remove the $resource from the chained list.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\AbstractResource $resource
     */
    public function removePosition(AbstractResource $resource)
    {
        $next = $resource->getNext();
        $previous = $resource->getPrevious();

        if ($next != null) {
            if ($previous !== null) {
                $next->setPrevious($previous);
            } else {
                $next->setPrevious();
            }
            $this->em->persist($next);
        }

        if ($previous !== null) {
            if ($next !== null) {
                $previous->setNext($next);
            } else {
                $previous->setNext();
            }
            $this->em->persist($previous);
        }
    }

    /**
     * Insert the resource $resource before the target $target.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\AbstractResource $resource
     * @param \Claroline\CoreBundle\Entity\Resource\AbstractResource $target
     */
    public function insertBefore(AbstractResource $resource, AbstractResource $next = null)
    {
        if ($resource->getParent() === null) {
            throw new \Exception('The root directories cannot be sorted');
        }

        $oldNext = $resource->getNext();
        $oldPrevious = $resource->getPrevious();

        if ($next !== null) {
            $newPrevious = $next->getPrevious();
        } else {
            $newPrevious = $this->em
                ->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
                ->findOneBy(array('parent' => $resource->getParent(), 'next' => null));
        }

        $resource->setNext($next);
        $resource->setPrevious($newPrevious);

        if ($newPrevious !== null) {
            $newPrevious->setNext($resource);
            $this->em->persist($newPrevious);
        }

        if ($oldPrevious !== null) {
            $oldPrevious->setNext($oldNext);
            $this->em->persist($oldPrevious);
        }

        if ($oldNext !== null) {
            $oldNext->setPrevious($oldPrevious);
            $this->em->persist($oldNext);
        }

        if ($next !== null) {
            $next->setPrevious($resource);
            $this->em->persist($next);
        }

        $this->em->persist($resource);
        $this->em->flush();
    }
}