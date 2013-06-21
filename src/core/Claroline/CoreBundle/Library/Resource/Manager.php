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
    /** @var Utilities */
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
        $this->ut = $container->get('claroline.resource.utilities');
        $this->ic = $container->get('claroline.resource.icon_creator');
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
    public function setResourceRights(AbstractResource $resource, array $rights, array $roles = array())
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

            if (count($roles) === 0) {
                $role = $roleRepo->findOneBy(array('name' => $role.'_'.$workspace->getId()));
            } else {
                $role = $roles[$role.'_'.$workspace->getId()];
            }
            $this->createRight($permissions, false, $role, $resource, $resourceTypes, false);
        }

        $anonymousPerms = array(
            'canCopy' => false,
            'canDelete' => false,
            'canOpen' => false,
            'canExport' => false,
            'canEdit' => false
        );

        $this->createRight(
            $anonymousPerms,
            false,
            $roleRepo->findOneBy(array('name' => 'ROLE_ANONYMOUS')),
            $resource,
            array(),
            false
        );

        $resourceTypes = $resourceTypeRepo->findAll();

        $adminPerms = array(
            'canCopy' => true,
            'canDelete' => true,
            'canOpen' => true,
            'canExport' => true,
            'canEdit' => true
        );

        $this->createRight(
            $adminPerms,
            false,
            $roleRepo->findOneBy(array('name' => 'ROLE_ADMIN')),
            $resource,
            $resourceTypes,
            false
        );
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

    public function createRootDir(AbstractWorkspace $workspace, User $user, array $configPermsRootDir, array $roles = array())
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
        $this->setResourceRights($rootDir, $configPermsRootDir, $roles);
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

    /**
     * Create a new ResourceRight
     *
     * @param array $permissions
     * @param boolean $isRecursive
     * @param \Claroline\CoreBundle\Entity\Role $role
     * @param \Claroline\CoreBundle\Entity\Resource\AbstractResource $resource
     */
    public function createRight(
        array $permissions,
        $isRecursive,
        Role $role,
        AbstractResource $resource,
        array $resourceTypes = array(),
        $autoflush = true
    )
    {
        $resourceRights = array();

        if ($isRecursive) {
            $resourceRights = $this->findAndCreateMissingDescendants($role, $resource);
        } else {
                $resourceRight = new ResourceRights();
                $resourceRight->setRole($role);
                $resourceRight->setResource($resource);
                $resourceRights[] = $resourceRight;
        }

        foreach ($resourceRights as $resourceRight) {
            $resourceRight->setCanCopy($permissions['canCopy']);
            $resourceRight->setCanOpen($permissions['canOpen']);
            $resourceRight->setCanDelete($permissions['canDelete']);
            $resourceRight->setCanEdit($permissions['canEdit']);
            $resourceRight->setCanExport($permissions['canExport']);
            $resourceRight->setCreatableResourceTypes($resourceTypes);

            $this->em->persist($resourceRight);
        }

        if ($autoflush) {
            $this->em->flush();
        }
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Role $role
     * @param \Claroline\CoreBundle\Entity\Resource\AbstractResource $resource
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceRights
     */
    public function findAndCreateMissingDescendants(Role $role, AbstractResource $resource)
    {
        $resourceRepo = $this->em->getRepository('ClarolineCoreBundle:Resource\AbstractResource');
        $alreadyExistings = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceRights')
            ->findRecursiveByResourceAndRole($resource, $role);
        $descendants = $resourceRepo->findDescendants($resource, true);
        $finalRights = array();

        foreach ($descendants as $descendant) {
            $found = false;
            foreach ($alreadyExistings as $existingRight) {
                if ($existingRight->getResource() === $descendant) {
                    $finalRights[] = $existingRight;
                    $found = true;
                }
            }

            if (!$found) {
                $resourceRight = new ResourceRights();
                $resourceRight->setRole($role);
                $resourceRight->setResource($descendant);
                $finalRights[] = $resourceRight;
            }
        }

        return $finalRights;
    }

    /**
     * Sort every children of a resource.
     *
     * @param array $resources
     *
     * @return array
     */
    public function findAndSortChildren(AbstractResource $parent)
    {
        //a little bit hacky but retrieve all children of the parent
        $resources = $this->resourceRepo->findChildren($parent, array('ROLE_ADMIN'));
        $sorted = array();
        //set the 1st item.
        foreach ($resources as $resource) {
            if ($resource['previous_id'] === null) {
                $sorted[] = $resource;
            }
        }

        $resourceCount = count($resources);
        $sortedCount = 0;

        for ($i = 0; $sortedCount < $resourceCount; ++$i) {
            $sortedCount = count($sorted);

            foreach ($resources as $resource) {
                if ($sorted[$sortedCount - 1]['id'] === $resource['previous_id']) {
                    $sorted[] = $resource;
                }
            }

            if ($i > 100) {
                throw new \Exception('More than 100 items in a directory or infinite loop detected');
            }
        }

        return $sorted;
    }

    /**
     * Sort an array of serialized resources. The chained list can have some "holes".
     *
     * @param array $resources
     *
     * @return array
     */
    public function sort(array $resources)
    {
        if ($this->sameParents($resources)) {
            $parent = $this->resourceRepo->find($resources[0]['parent_id']);
            $sortedList = $this->findAndSortChildren($parent);

            foreach ($sortedList as $sortedItem) {
                foreach ($resources as $resource) {
                    if ($resource['id'] === $sortedItem['id']) {
                        $sortedRes[] = $resource;
                    }
                }
            }

        } else {
            throw new \Exception("These resources don't share the same parent");
        }

        return $sortedRes;
    }

    /**
     * Checks if an array of serialized resources share the same parent.
     *
     * @param array $resources
     *
     * @return array
     */
    public function sameParents(array $resources)
    {
        $firstRes = array_pop($resources);
        $tmp = $firstRes['parent_id'];

        foreach ($resources as $resource) {
            if ($tmp !== $resource['parent_id']) {
                return false;
            }
        }

        return true;
    }
}
