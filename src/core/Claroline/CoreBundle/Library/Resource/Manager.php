<?php

namespace Claroline\CoreBundle\Library\Resource;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManager;
use Gedmo\Exception\UnexpectedValueException;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\ResourceRights;
use Claroline\CoreBundle\Entity\Resource\ResourceShortcut;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Library\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Library\Event\CopyResourceEvent;
use Claroline\CoreBundle\Library\Event\LogResourceCreateEvent;
use Claroline\CoreBundle\Library\Event\LogResourceMoveEvent;
use Claroline\CoreBundle\Library\Event\LogResourceDeleteEvent;
use Claroline\CoreBundle\Library\Event\LogResourceCopyEvent;
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
    /** @var Utilities */
    private $ut;
    /** @var IconCreator */
    private $ic;

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
     * @param array             $rights         And array of rights
     *
     * @return  AbstractResource
     *
     * @throws \Exception
     */
    public function create(
        AbstractResource $resource,
        $parentId,
        $resourceType,
        User $user = null,
        $rights = null,
        $autoflush = true,
        $autolog = true
    )
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

        if ($parent !== null) {
            $this->setLastPosition($parent, $resource);
        }

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
        if ($rights === null) {
            $this->cloneRights($parent, $resource);
        } else {
            $this->setResourceRights($resource, $rights);
        }

        if ($autoflush) {
            $this->em->flush();
        }

        if ($autolog) {
            $log = new LogResourceCreateEvent($resource);
            $this->ed->dispatch('log', $log);
        }

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
        $this->removePosition($child);
        $this->setLastPosition($parent, $child);
        $oldParent = $child->getParent();
        $child->setParent($parent);
        $rename = $this->ut->getUniqueName($child, $parent);
        $child->setName($rename);
        $rights = $child->getRights();
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
     * Copies the resource rights from $resource to $target.
     *
     * @param AbstractResource $resource
     * @param AbstractResource $target
     *
     */
    public function cloneRights(AbstractResource $resource, AbstractResource $target)
    {
        $resourceRights = $this->em
            ->getRepository('ClarolineCoreBundle:Resource\ResourceRights')
            ->findBy(array('resource' => $resource));
        foreach ($resourceRights as $resourceRight) {
            $rc = new ResourceRights();
            $rc->setRole($resourceRight->getRole());
            $rc->setResource($target);
            $rc->setRightsFrom($resourceRight);

            if ($target->getResourceType()->getName() === 'directory') {
                $rc->setCreatableResourceTypes($resourceRight->getCreatableResourceTypes()->toArray());
            }

            $this->em->persist($rc);
        }

        $this->em->persist($target);
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
     * Sets the resource rights of a resource.
     * Expects an array of role of the following form:
     * array('ROLE_WS_MANAGER' => array('canOpen' => true, 'canEdit' => false', ...)
     * The 'canCopy' key must contain an array of resourceTypes name.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\AbstractResource $resource
     * @param array $rights
     */
    public function setResourceRights(AbstractResource $resource, array $rights)
    {
        $roleRepo = $this->em->getRepository('ClarolineCoreBundle:Role');
        $resourceTypeRepo = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceType');
        $workspace = $resource->getWorkspace();

        foreach ($rights as $role => $permissions) {
            $resourceTypes = array();
            $unknownTypes = array();

            foreach ($permissions['canCreate'] as $type) {
                $rt = $resourceTypeRepo->findOneByName($type);
                if ($rt === null) {
                    $unknownTypes[] = $type['name'];
                }
                $resourceTypes[] = $rt;
            }

            if (count($unknownTypes) > 0) {
                $content = "The resource type(s) ";
                foreach ($unknownTypes as $unknown) {
                    $content .= "{$unknown}, ";
                }
                $content .= "were not found";

                throw new \Exception($content);
            }

            $role = $roleRepo->findOneBy(array('name' => $role.'_'.$workspace->getId()));
            $this->createDefaultsResourcesRights(
                $permissions['canDelete'],
                $permissions['canOpen'],
                $permissions['canEdit'],
                $permissions['canCopy'],
                $permissions['canExport'],
                $role,
                $resource,
                $resourceTypes
            );
        }

        $this->createDefaultsResourcesRights(
            false, false, false, false, false,
            $roleRepo->findOneBy(array('name' => 'ROLE_ANONYMOUS')),
            $resource,
            array()
        );

        $resourceTypeRepo->findAll();

        $this->createDefaultsResourcesRights(
            true, true, true, true, true,
            $roleRepo->findOneBy(array('name' => 'ROLE_ADMIN')),
            $resource,
            $resourceTypes
        );
    }

    /**
     * Create default permissions for a role and a resource.
     *
     * @param boolean $canDelete
     * @param boolean $canOpen
     * @param boolean $canEdit
     * @param boolean $canCopy
     * @param boolean $canExport
     * @param boolean $canCreate
     * @param \Claroline\CoreBundle\Entity\Role $role
     * @param \Claroline\CoreBundle\Entity\Resource\AbstractResource $resource
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceRights
     */
    private function createDefaultsResourcesRights(
        $canDelete,
        $canOpen,
        $canEdit,
        $canCopy,
        $canExport,
        Role $role,
        AbstractResource $resource,
        array $resourceTypes
    )
    {
        $rights = new ResourceRights();
        $rights->setCanCopy($canCopy);
        $rights->setCanDelete($canDelete);
        $rights->setCanEdit($canEdit);
        $rights->setCanOpen($canOpen);
        $rights->setCanExport($canExport);
        $rights->setRole($role);
        $rights->setResource($resource);
        $rights->setCreatableResourceTypes($resourceTypes);
        $this->em->persist($rights);
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

    public function createRootDir(AbstractWorkspace $workspace, User $user, array $configPermsRootDir)
    {
        $rootDir = new Directory();
        $rootDir->setName("{$workspace->getName()} - {$workspace->getCode()}");
        $rootDir->setCreator($user);
        $directoryType = $this->em
            ->getRepository('ClarolineCoreBundle:Resource\ResourceType')
            ->findOneBy(array('name' => 'directory'));
        $directoryIcon = $this->em
            ->getRepository('ClarolineCoreBundle:Resource\ResourceIcon')
            ->findOneBy(array('type' => 'directory', 'iconType' => 1));
        $rootDir->setIcon($directoryIcon);
        $rootDir->setResourceType($directoryType);
        $rootDir->setWorkspace($workspace);
        $this->setResourceRights($rootDir, $configPermsRootDir);
        $this->em->persist($rootDir);

        return $rootDir;
    }

    public function makeShortcut(AbstractResource $resource, Directory $parent, User $creator)
    {
        $shortcut = new ResourceShortcut();
        $shortcut->setParent($parent);
        $shortcut->setCreator($creator);
        $shortcut->setIcon($resource->getIcon()->getShortcutIcon());
        $shortcut->setName($resource->getName());
        $shortcut->setName($this->ut->getUniqueName($shortcut, $parent));
        $shortcut->setWorkspace($parent->getWorkspace());
        $shortcut->setResourceType($resource->getResourceType());

        if ($parent !== null) {
            $this->setLastPosition($parent, $shortcut);
        }

        if (get_class($resource) !== 'Claroline\CoreBundle\Entity\Resource\ResourceShortcut') {
            $shortcut->setResource($resource);
        } else {
            $shortcut->setResource($resource->getResource());
        }

        $this->cloneRights($shortcut->getParent(), $shortcut);
        $this->em->persist($shortcut);

        return $shortcut;
    }

    public function isPathValid(array $ancestors)
    {
        $continue = true;

        for ($i = 0, $size = count($ancestors); $i < $size; $i++) {

            if (isset($ancestors[$i+1])) {
                if ($ancestors[$i+1]->getParent() == $ancestors[$i]) {
                    $continue = true;
                } else {
                    var_dump($ancestors[$i+1]->getName());
                    if ($this->hasLinkTo($ancestors[$i], $ancestors[$i+1])) {
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

    private function hasLinkTo(Directory $parent, Directory $target) {
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
     * Set the $resource at the last position of the $parent.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\AbstractResource $parent
     * @param \Claroline\CoreBundle\Entity\Resource\AbstractResource $resource
     */
    public function setLastPosition(AbstractResource $parent, AbstractResource $resource)
    {
        $lastChild = $this->em
            ->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
            ->findOneBy(array('parent' => $parent, 'next' => null));
        if ($lastChild !== null) {
            $resource->setPrevious($lastChild);
            $lastChild->setNext($resource);
            $this->em->persist($lastChild);
        } else {
            $resource->setPrevious();
        }
        $resource->setNext();

        $this->em->persist($resource);
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
    {   /*
        if ($resource->getParent() !== $next->getParent()) {
            throw new \Exception('Both sorted resources must be located in the same directory');
        }*/

        if ($resource->getParent() === null) {
            throw new \Exception('The root directories cannot be sorted');
        }

        $resource->setNext($next);
//                var_dump('resource next is '.$resource->getNext()->getName());
        $savePrevious = $resource->getPrevious();

        if ($next !== null) {
            $previous = $next->getPrevious();
            $next->setPrevious($resource);
//            var_dump('next name ='.$next->getName());
//            var_dump('next previous = '.$next->getPrevious()->getName());
            $this->em->persist($next);
        } else {
//            var_dump('next is null');
            $previous = $this->em
                ->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
                ->findOneBy(array('parent' => $resource->getParent(), 'next' => null));
        }

        $resource->setPrevious($previous);

//        if ($resource->getPrevious()!== null) {
//             var_dump($resource->getPrevious()->getName());
//        } else {
//            var_dump('resource previous is null');
//        }

        if ($previous !== null) {
            $previous->setNext($resource);
            $previous->setPrevious($savePrevious);
            $this->em->persist($previous);
        }

//        var_dump('resource next is '.$resource->getNext()->getName());

        $this->em->persist($resource);
        $this->em->flush();
    }
}
