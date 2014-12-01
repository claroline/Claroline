<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Resource\ResourceShortcut;
use Claroline\CoreBundle\Entity\Resource\ResourceIcon;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Repository\ResourceTypeRepository;
use Claroline\CoreBundle\Repository\ResourceNodeRepository;
use Claroline\CoreBundle\Repository\ResourceRightsRepository;
use Claroline\CoreBundle\Repository\ResourceShortcutRepository;
use Claroline\CoreBundle\Repository\RoleRepository;
use Claroline\CoreBundle\Manager\Exception\MissingResourceNameException;
use Claroline\CoreBundle\Manager\Exception\ResourceTypeNotFoundException;
use Claroline\CoreBundle\Manager\Exception\RightsException;
use Claroline\CoreBundle\Manager\Exception\ExportResourceException;
use Claroline\CoreBundle\Manager\Exception\WrongClassException;
use Claroline\CoreBundle\Manager\Exception\ResourceMoveException;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Utilities\ClaroUtilities;
use Claroline\CoreBundle\Library\Security\Utilities;
use Symfony\Component\Security\Core\SecurityContextInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @DI\Service("claroline.manager.resource_manager")
 */
class ResourceManager
{
    /** @var RightsManager */
    private $rightsManager;
    /** @var ResourceTypeRepository */
    private $resourceTypeRepo;
    /** @var ResourceNodeRepository */
    private $resourceNodeRepo;
    /** @var ResourceRightsRepository */
    private $resourceRightsRepo;
    /** @var ResourceShortcutRepository */
    private $shortcutRepo;
    /** @var RoleRepository */
    private $roleRepo;
    /** @var RoleManager */
    private $roleManager;
    /** @var MaskManager */
    private $maskManager;
    /** @var IconManager */
    private $iconManager;
    /** @var Dispatcher */
    private $dispatcher;
    /** @var ObjectManager */
    private $om;
    /** @var ClaroUtilities */
    private $ut;
    /** @var Utilities */
    private $secut;
    private $container;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "roleManager"     = @DI\Inject("claroline.manager.role_manager"),
     *     "iconManager"     = @DI\Inject("claroline.manager.icon_manager"),
     *     "maskManager"     = @DI\Inject("claroline.manager.mask_manager"),
     *     "container"       = @DI\Inject("service_container"),
     *     "rightsManager"   = @DI\Inject("claroline.manager.rights_manager"),
     *     "dispatcher"      = @DI\Inject("claroline.event.event_dispatcher"),
     *     "om"              = @DI\Inject("claroline.persistence.object_manager"),
     *     "ut"              = @DI\Inject("claroline.utilities.misc"),
     *     "secut"           = @DI\Inject("claroline.security.utilities")
     * })
     */
    public function __construct (
        RoleManager $roleManager,
        IconManager $iconManager,
        ContainerInterface $container,
        RightsManager $rightsManager,
        StrictDispatcher $dispatcher,
        ObjectManager $om,
        ClaroUtilities $ut,
        Utilities $secut,
        MaskManager $maskManager
    )
    {
        $this->resourceTypeRepo = $om->getRepository('ClarolineCoreBundle:Resource\ResourceType');
        $this->resourceNodeRepo = $om->getRepository('ClarolineCoreBundle:Resource\ResourceNode');
        $this->resourceRightsRepo = $om->getRepository('ClarolineCoreBundle:Resource\ResourceRights');
        $this->roleRepo = $om->getRepository('ClarolineCoreBundle:Role');
        $this->shortcutRepo = $om->getRepository('ClarolineCoreBundle:Resource\ResourceShortcut');
        $this->roleManager = $roleManager;
        $this->iconManager = $iconManager;
        $this->rightsManager = $rightsManager;
        $this->maskManager = $maskManager;
        $this->dispatcher = $dispatcher;
        $this->om = $om;
        $this->ut = $ut;
        $this->secut = $secut;
        $this->container = $container;
    }

    /**
     * Creates a resource.
     *
     * array $rights should be defined that way:
     * array('ROLE_WS_XXX' => array('open' => true, 'edit' => false, ...
     * 'create' => array('directory', ...), 'role' => $entity))
     *
     * @param \Claroline\CoreBundle\Entity\Resource\AbstractResource   $resource
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceType       $resourceType
     * @param \Claroline\CoreBundle\Entity\User                        $creator
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode       $parent
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceIcon       $icon
     * @param array                                                    $rights
     *
     * @return \Claroline\CoreBundle\Entity\Resource\AbstractResource
     */
    public function create(
        AbstractResource $resource,
        ResourceType $resourceType,
        User $creator,
        Workspace $workspace,
        ResourceNode $parent = null,
        ResourceIcon $icon = null,
        array $rights = array(),
        $isPublished = true
    )
    {
        $this->om->startFlushSuite();
        $this->checkResourcePrepared($resource);
        $node = $this->om->factory('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $node->setResourceType($resourceType);
        $node->setPublished($isPublished);

        $mimeType = ($resource->getMimeType() === null) ?
            'custom/' . $resourceType->getName():
            $resource->getMimeType();

        $node->setMimeType($mimeType);
        $node->setName($resource->getName());
        $name = $this->getUniqueName($node, $parent);

        $previous = $parent === null ?
            null:
            $this->resourceNodeRepo->findOneBy(array('parent' => $parent, 'next' => null));

        if ($previous) {
            $previous->setNext($node);
        }

        $node->setCreator($creator);
        $node->setWorkspace($workspace);
        $node->setParent($parent);
        $node->setName($name);
        $node->setPrevious($previous);
        $node->setClass(get_class($resource));

        if (!is_null($parent)) {
            $node->setAccessibleFrom($parent->getAccessibleFrom());
            $node->setAccessibleUntil($parent->getAccessibleUntil());
        }

        $resource->setResourceNode($node);
        $this->setRights($node, $parent, $rights);
        $this->om->persist($node);
        $this->om->persist($resource);

        if ($icon === null) {
            $icon = $this->iconManager->getIcon($resource, $workspace);
        }

        $parentPath = '';

        if ($parent) {
            $parentPath .= $parent->getPathForDisplay() . ' / ';
        }

        $node->setPathForCreationLog($parentPath . $name);
        $node->setIcon($icon);

        //if it's an activity, initialize the permissions for its linked resources;
        if ($resourceType->getName() === 'activity') {
            $this->container->get('claroline.manager.activity_manager')->initializePermissions($resource);
        }

        $this->dispatcher->dispatch('log', 'Log\LogResourceCreate', array($node));
        $this->om->endFlushSuite();

        return $resource;
    }

    /**
     * Gets a unique name for a resource in a folder.
     * If the name of the resource already exists here, ~*indice* will be appended
     * to its name.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $node
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $parent
     * @param bool $isCopy
     * @return string
     */
    public function getUniqueName(ResourceNode $node, ResourceNode $parent = null, $isCopy = false)
    {
        $candidateName = $node->getName();
        $nodeType = $node->getResourceType();
        //if the parent is null, then it's a workspace root and the name is always correct
        //otherwise we fetch each workspace root with the findBy and the UnitOfWork won't be happy...
        if (!$parent) return $candidateName;

        $parent = $parent ?: $node->getParent();
        $sameLevelNodes = $parent ?
            $parent->getChildren() :
            $this->resourceNodeRepo->findBy(array('parent' => null));
        $siblingNames = array();

        foreach ($sameLevelNodes as $levelNode) {
            if (!$isCopy && $levelNode === $node) {
                // without that condition, a node which is "renamed" with the
                // same name is also incremented
                continue;
            }
            if ($levelNode->getResourceType() === $nodeType) {
                $siblingNames[] = $levelNode->getName();
            }
        }

        if (!in_array($candidateName, $siblingNames)) {
            return $candidateName;
        }

        $candidateRoot = pathinfo($candidateName, PATHINFO_FILENAME);
        $candidateExt = ($ext = pathinfo($candidateName, PATHINFO_EXTENSION)) ? '.' . $ext : '';
        $candidatePattern = '/^'
            . preg_quote($candidateRoot)
            . '~(\d+)'
            . preg_quote($candidateExt)
            . '$/';
        $previousIndex = 0;

        foreach ($siblingNames as $name) {
            if (preg_match($candidatePattern, $name, $matches) && $matches[1] > $previousIndex) {
                $previousIndex = $matches[1];
            }
        }

        return $candidateRoot . '~' . ++$previousIndex . $candidateExt;
    }

    /**
     * Checks if an array of serialized resources share the same parent.
     *
     * @param array nodes
     *
     * @return array
     */
    public function haveSameParents(array $nodes)
    {
        $firstRes = array_pop($nodes);
        $tmp = $firstRes['parent_id'];

        foreach ($nodes as $node) {
            if ($tmp !== $node['parent_id']) {
                return false;
            }
        }

        return true;
    }

    /**
     * Sort every children of a resource.
     *
     * @param ResourceNode $parent
     *
     * @return array
     */
    public function findAndSortChildren(ResourceNode $parent, User $user)
    {
        //a little bit hacky but retrieve all children of the parent
        $nodes = $this->resourceNodeRepo
            ->findChildren($parent, array('ROLE_ADMIN'), $user);
        $sorted = array();
        //set the 1st item.
        foreach ($nodes as $node) {
            if ($node['previous_id'] === null) {
                $sorted[] = $node;
            }
        }

        $resourceCount = count($nodes);
        $sortedCount = 0;

        for ($i = 0; $sortedCount < $resourceCount; ++$i) {
            $sortedCount = count($sorted);

            foreach ($nodes as $node) {
                if ($sorted[$sortedCount - 1]['id'] === $node['previous_id']) {
                    $sorted[] = $node;
                }
            }

            if ($i > 100) {
                $this->restoreNodeOrder($parent);

                return $nodes;
            }
        }

        return $sorted;
    }

    /**
     * Sort an array of serialized resources. The chained list can have some "holes".
     *
     * @param array $nodes
     *
     * @throws \Exception
     *
     * @return array
     */
    public function sort(array $nodes, User $user)
    {
        $sortedResources = array();

        if (count($nodes) > 0) {
            if ($this->haveSameParents($nodes)) {
                $parent = $this->resourceNodeRepo->find($nodes[0]['parent_id']);
                $sortedList = $this->findAndSortChildren($parent, $user);

                foreach ($sortedList as $sortedItem) {
                    foreach ($nodes as $node) {
                        if ($node['id'] === $sortedItem['id']) {
                            $sortedResources[] = $node;
                        }
                    }
                }
            } else {
                throw new \Exception("These resources don't share the same parent");
            }
        }

        return $sortedResources;
    }

    /**
     * Creates a shortcut.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode     $target
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode     $parent
     * @param \Claroline\CoreBundle\Entity\User                      $creator
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceShortcut $shortcut
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceShortcut
     */
    public function makeShortcut(ResourceNode $target, ResourceNode $parent, User $creator, ResourceShortcut $shortcut)
    {
        $shortcut->setName($target->getName());

        if (get_class($target) !== 'Claroline\CoreBundle\Entity\Resource\ResourceShortcut') {
            $shortcut->setTarget($target);
        } else {
            $shortcut->setTarget($target->getTarget());
        }

        $shortcut = $this->create(
            $shortcut,
            $target->getResourceType(),
            $creator,
            $parent->getWorkspace(),
            $parent,
            $target->getIcon()->getShortcutIcon()
        );

        $this->dispatcher->dispatch('log', 'Log\LogResourceCreate', array($shortcut->getResourceNode()));

        return $shortcut;
    }

    /**
     * Set the right of a resource.
     * If $rights = array(), the $parent node rights will be copied.
     *
     * array $rights should be defined that way:
     * array('ROLE_WS_XXX' => array('open' => true, 'edit' => false, ...
     * 'create' => array('directory', ...), 'role' => $entity))
     *
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $node
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $parent
     * @param array                                              $rights
     *
     * @throws RightsException
     */
    public function setRights(
        ResourceNode $node,
        ResourceNode $parent = null,
        array $rights = array()
    )
    {
        if (count($rights) === 0 && $parent !== null) {
            $node = $this->rightsManager->copy($parent, $node);
        } else {
//            if (count($rights) === 0) {
//                throw new RightsException('Rights must be specified if there is no parent');
//            }
            $this->createRights($node, $rights);
        }

        return $node;
    }

    /**
     * Create the rights for a node.
     *
     * array $rights should be defined that way:
     * array('ROLE_WS_XXX' => array('open' => true, 'edit' => false, ...
     * 'create' => array('directory', ...), 'role' => $entity))
     *
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $node
     * @param array                                              $rights
     */
    public function createRights(
        ResourceNode $node,
        array $rights = array()
    )
    {
        foreach ($rights as $data) {
            $resourceTypes = $this->checkResourceTypes($data['create']);
            $this->rightsManager->create($data, $data['role'], $node, false, $resourceTypes);
        }

        $this->rightsManager->create(
            0,
            $this->roleRepo->findOneBy(array('name' => 'ROLE_ANONYMOUS')),
            $node,
            false,
            array()
        );

        $this->rightsManager->create(
            0,
            $this->roleRepo->findOneBy(array('name' => 'ROLE_USER')),
            $node,
            false,
            array()
        );
    }

    /**
     * Checks if a resource already has a name.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\AbstractResource $resource
     *
     * @throws MissingResourceNameException
     */
    public function checkResourcePrepared(AbstractResource $resource)
    {
        $stringErrors = '';

        //null or '' shouldn't be valid
        if ($resource->getName() == null) {
            $stringErrors .= 'The resource name is missing' . PHP_EOL;
        }

        if ($stringErrors !== '') {
            throw new MissingResourceNameException($stringErrors);
        }
    }

    /**
     * Checks if an array of resource type name exists.
     * Expects an array of types array(array('name' => 'type'),...).
     *
     * @param array $resourceTypes
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceType
     *
     * @throws ResourceTypeNotFoundException
     */
    public function checkResourceTypes(array $resourceTypes)
    {
        $validTypes = array();
        $unknownTypes = array();

        foreach ($resourceTypes as $type) {
            //@todo write findByNames method.
            $rt = $this->resourceTypeRepo->findOneByName($type['name']);
            if ($rt === null) {
                $unknownTypes[] = $type['name'];
            } else {
                $validTypes[] = $rt;
            }
        }

        if (count($unknownTypes) > 0) {
            $content = "The resource type(s) ";
            foreach ($unknownTypes as $unknown) {
                $content .= "{$unknown}, ";
            }
            $content .= "were not found";

            throw new ResourceTypeNotFoundException($content);
        }

        return $validTypes;
    }

    /**
     * Insert the resource $resource before the target $next.
     *
     * @param ResourceNode $node
     * @param ResourceNode $next
     *
     * @return ResourceNode
     */
    public function insertBefore(ResourceNode $node, ResourceNode $next = null)
    {
        $previous = $this->findPreviousOrLastRes($node->getParent(), $next);
        $oldPrev = $node->getPrevious();
        $oldNext = $node->getNext();

        if ($next) {
            $this->removePreviousWherePreviousIs($node);
            $node->setNext(null);
            $next->setPrevious($node);
            $this->om->persist($next);
        }

        if ($previous) {
            $this->removeNextWhereNextIs($node);
            $previous->setNext($node);
            $this->om->persist($previous);
        } else {
            $node->setPrevious(null);
            $node->setNext(null);
        }

        if ($oldPrev) {
            $this->removePreviousWherePreviousIs($oldPrev);
            $oldPrev->setNext($oldNext);
            $this->om->persist($oldPrev);
        }

        if ($oldNext) {
            $this->removePreviousWherePreviousIs($oldPrev);
            $oldNext->setPrevious($oldPrev);
            $this->om->persist($oldNext);
        }

        $node->setNext($next);
        $node->setPrevious($previous);
        $this->om->persist($node);

        $this->om->flush();

        return $node;
    }

    /**
     * Moves a resource.
     *
     * @param ResourceNode $child currently treated node
     * @param ResourceNode $parent old parent
     *
     * @throws ResourceMoveException
     *
     * @return ResourceNode
     */
    public function move(ResourceNode $child, ResourceNode $parent)
    {
        if ($parent === $child) {
            throw new ResourceMoveException("You cannot move a directory into itself");
        }
        $this->om->startFlushSuite();
        $this->removePosition($child);
        $this->setLastPosition($parent, $child);
        $child->setParent($parent);
        $child->setName($this->getUniqueName($child, $parent));

        if ($child->getWorkspace()->getId() !== $parent->getWorkspace()->getId()) {

            $this->updateWorkspace($child, $parent->getWorkspace());
        }
        $this->om->persist($child);
        $this->om->endFlushSuite();
        $this->dispatcher->dispatch('log', 'Log\LogResourceMove', array($child, $parent));

        return $child;
    }


    /**
     * Set the $node at the last position of the $parent.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $parent
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $node
     */
    public function setLastPosition(ResourceNode $parent, ResourceNode $node)
    {
        /** @var \Claroline\CoreBundle\Entity\Resource\ResourceNode $lastChild */
        $lastChild = $this->resourceNodeRepo->findOneBy(array('parent' => $parent, 'next' => null));

        $node->setPrevious($lastChild);
        $node->setNext(null);
        $this->om->persist($node);

        if ($lastChild) {
            $lastChild->setNext($node);
            $this->om->persist($lastChild);
        }

        $this->om->flush();
    }

    /**
     * Remove the $node from the chained list.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $node
     */
    public function removePosition(ResourceNode $node)
    {
        $next = $node->getNext();
        $previous = $node->getPrevious();
        $node->setPrevious(null);
        $node->setNext(null);
        $this->om->persist($node);

        if ($previous) {
            $previous->setNext($next);
            $this->om->persist($previous);
        }

        if ($next) {
            $next->setPrevious($previous);
            $this->om->persist($next);          
        }

        $this->om->forceFlush();
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $parent
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $node
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceNode
     */
    public function findPreviousOrLastRes(ResourceNode $parent, ResourceNode $node = null)
    {
        return ($node !== null) ?
            $node->getPrevious():
            $this->resourceNodeRepo->findOneBy(array('parent' => $parent, 'next' => null));
    }

    /**
     * Checks if a resource in a node has a link to the target with a shortcut.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $parent
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $target
     *
     * @return boolean
     */
    public function hasLinkTo(ResourceNode $parent, ResourceNode $target)
    {
        $nodes = $this->resourceNodeRepo
            ->findBy(array('parent' => $parent, 'class' => 'Claroline\CoreBundle\Entity\Resource\ResourceShortcut'));

        foreach ($nodes as $node) {
            $shortcut = $this->getResourceFromNode($node);
            if ($shortcut->getTarget() == $target) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if a path is valid.
     *
     * @param array $ancestors
     *
     * @return boolean
     */
    public function isPathValid(array $ancestors)
    {
        $continue = true;

        for ($i = 0, $size = count($ancestors); $i < $size; $i++) {
            if (isset($ancestors[$i + 1])) {
                if ($ancestors[$i + 1]->getParent() === $ancestors[$i]) {
                    $continue = true;
                } else {
                    $continue = $this->hasLinkTo($ancestors[$i], $ancestors[$i + 1]);
                }
            }

            if (!$continue) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks if all the resource in the array are directories.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode[] $ancestors
     *
     * @return boolean
     */
    public function areAncestorsDirectory(array $ancestors)
    {
        array_pop($ancestors);

        foreach ($ancestors as $ancestor) {
            if ($ancestor->getResourceType()->getName() !== 'directory') {
                return false;
            }
        }

        return true;
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
        $allowedArrayCriteria = array('types');
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
     * Copies a resource in a directory.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $node
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $parent
     * @param \Claroline\CoreBundle\Entity\User                  $user
     * @param boolean $withRights
     * Defines if the rights of the copied resource have to be created
     * @param boolean $withDirectoryContent
     * Defines if the content of a directory has to be copied too
     * @param array $rights
     * If defined, the copied resource will have exactly the given rights
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceNode
     */
    public function copy(
        ResourceNode $node,
        ResourceNode $parent,
        User $user,
        $withRights = true,
        $withDirectoryContent = true,
        array $rights = array()
    )
    {
        $last = $this->resourceNodeRepo->findOneBy(array('parent' => $parent, 'next' => null));
        $resource = $this->getResourceFromNode($node);

        if ($resource instanceof ResourceShortcut) {
            $copy = $this->om->factory('Claroline\CoreBundle\Entity\Resource\ResourceShortcut');
            $copy->setTarget($resource->getTarget());
            $newNode = $this->copyNode($node, $parent, $user, $last, $withRights, $rights);
            $copy->setResourceNode($newNode);

        } else {
            $event = $this->dispatcher->dispatch(
                'copy_' . $node->getResourceType()->getName(),
                'CopyResource',
                array($resource, $parent)
            );

            $copy = $event->getCopy();
            $newNode = $this->copyNode($node, $parent, $user, $last, $withRights, $rights);
            $copy->setResourceNode($newNode);

            if ($node->getResourceType()->getName() == 'directory' &&
                $withDirectoryContent) {

                foreach ($node->getChildren() as $child) {
                    $this->copy($child, $newNode, $user, $withRights, $withDirectoryContent, $rights);
                }
            }
        }

        $this->om->persist($copy);

        if ($last) {
            $last->setNext($newNode);
            $this->om->persist($last);
        }

        $this->dispatcher->dispatch('log', 'Log\LogResourceCopy', array($newNode, $node));
        $this->om->flush();

        return $copy;
    }

    /**
     * Convert a resource into an array (mainly used to be serialized and sent to the manager.js as
     * a json response)
     *
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $node
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @return array
     */
    public function toArray(ResourceNode $node, TokenInterface $token)
    {
        $resourceArray = array();
        $resourceArray['id'] = $node->getId();
        $resourceArray['name'] = $node->getName();
        $resourceArray['parent_id'] = ($node->getParent() != null) ? $node->getParent()->getId() : null;
        $resourceArray['creator_username'] = $node->getCreator()->getUsername();
        $resourceArray['type'] = $node->getResourceType()->getName();
        $resourceArray['large_icon'] = $node->getIcon()->getRelativeUrl();
        $resourceArray['path_for_display'] = $node->getPathForDisplay();
        $resourceArray['mime_type'] = $node->getMimeType();
        $resourceArray['published'] = $node->isPublished();

        if ($node->getPrevious() !== null) {
            $resourceArray['previous_id'] = $node->getPrevious()->getId();
        }
        if ($node->getNext() !== null) {
            $resourceArray['next_id'] = $node->getNext()->getId();
        }

        $isAdmin = false;

        $roles = $this->roleManager->getStringRolesFromToken($token);

        foreach ($roles as $role) {
            if ($role === 'ROLE_ADMIN') {
                $isAdmin = true;
            }
        }

        if ($isAdmin || $node->getCreator()->getUsername() === $token->getUser()->getUsername()) {
            $resourceArray['mask'] = 1023;
        } else {
            $resourceArray['mask'] = $this->resourceRightsRepo->findMaximumRights($roles, $node);
        }

        return $resourceArray;
    }

    /**
     * Removes a resource.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $node
     * @throws \LogicException
     */
    public function delete(ResourceNode $node)
    {
        if ($node->getParent() === null) {
            throw new \LogicException('Root directory cannot be removed');
        }
        $workspace = $node->getWorkspace();
        $this->removePosition($node);
        $this->om->startFlushSuite();
        $nodes = $this->getDescendants($node);
        $nodes[] = $node;

        foreach ($nodes as $node) {

            $resource = $this->getResourceFromNode($node);
            /**
             * resChild can be null if a shortcut was removed
             * @todo: fix shortcut delete. If a target is removed, every link to the target should be removed too.
             */
            if ($resource !== null) {
                if ($node->getClass() !== 'Claroline\CoreBundle\Entity\Resource\ResourceShortcut') {
                    $event = $this->dispatcher->dispatch(
                        "delete_{$node->getResourceType()->getName()}",
                        'DeleteResource',
                        array($resource)
                    );

                    foreach ($event->getFiles() as $file) {
                        unlink($file);

                        $dir = $this->container->getParameter('claroline.param.files_directory') .
                            DIRECTORY_SEPARATOR .
                            $workspace->getCode();

                        if (is_dir($dir) && $this->isDirectoryEmpty($dir)) {
                            rmdir($dir);
                        }
                    }
                }

                $this->dispatcher->dispatch(
                    "log",
                    'Log\LogResourceDelete',
                    array($node)
                );

                if ($node->getIcon()) {
                    $this->iconManager->delete($node->getIcon(), $workspace);
                }

                /*
                 * If the child isn't removed here aswell, doctrine will fail to remove $resChild
                 * because it still has $resChild in its UnitOfWork or something (I have no idea
                 * how doctrine works tbh). So if you remove this line the suppression will
                 * not work for directory containing children.
                 */
                $this->om->remove($resource);
                $this->om->remove($node);
            }
        }

        if ($node->getIcon()) {
            $this->iconManager->delete($node->getIcon(), $workspace);
        }

        $this->om->remove($node);
        $this->om->endFlushSuite();
    }

    /**
     * Returns an archive with the required content.
     *
     * @param array $nodes[] the nodes being exported
     *
     * @throws ExportResourceException
     *
     * @return array
     */
    public function download(array $elements, $forceArchive = false)
    {
        $data = array();

        if (count($elements) === 0) {
            throw new ExportResourceException('No resources were selected.');
        }

        $archive = new \ZipArchive();
        $pathArch = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $this->ut->generateGuid() . '.zip';
        $archive->open($pathArch, \ZipArchive::CREATE);
        $nodes = $this->expandResources($elements);

        if (!$forceArchive && count($nodes) === 1) {
            $event = $this->dispatcher->dispatch(
                "download_{$nodes[0]->getResourceType()->getName()}",
                'DownloadResource',
                array($this->getResourceFromNode($nodes[0]))
            );

            $data['name'] = $nodes[0]->getName();
            $data['file'] = $event->getItem();
            $data['mimeType'] = $nodes[0]->getResourceType()->getName() === 'file' ?
                $nodes[0]->getMimeType():
                'text/plain';

            return $data;
        }

        if (isset($nodes[0])) {
            $currentDir = $nodes[0];
        } else {
            $archive->addEmptyDir($elements[0]->getName());
        }

        foreach ($nodes as $node) {

            $resource = $this->getResourceFromNode($node);

            if (get_class($resource) === 'Claroline\CoreBundle\Entity\Resource\ResourceShortcut') {
                $node = $resource->getTarget();
            }

            $filename = $this->getRelativePath($currentDir, $node) . $node->getName();
            $resource = $this->getResourceFromNode($node);

            //if it's a file, we may have to add the extension back in case someone removed it from the name
            if ($node->getResourceType()->getName() === 'file') {
                $extension = '.' . pathinfo($resource->getHashName(), PATHINFO_EXTENSION);
                if (!preg_match("#$extension#", $filename)) $filename .= $extension;
            }

            if ($node->getResourceType()->getName() !== 'directory') {
                $event = $this->dispatcher->dispatch(
                    "download_{$node->getResourceType()->getName()}",
                    'DownloadResource',
                    array($resource)
                );

                $obj = $event->getItem();

                if ($obj !== null) {
                    $archive->addFile($obj, iconv(mb_detect_encoding($filename), $this->getEncoding(), $filename));
                } else {
                     $archive->addFromString(iconv(mb_detect_encoding($filename), $this->getEncoding(), $filename), '');
                }
            } else {
                $archive->addEmptyDir(iconv(mb_detect_encoding($filename), $this->getEncoding(), $filename));
            }

            $this->dispatcher->dispatch('log', 'Log\LogResourceExport', array($node));
        }

        $archive->close();
        $data['name'] = 'archive.zip';
        $data['file'] = $pathArch;
        $data['mimeType'] = 'application/zip';

        return $data;
    }

    /**
     * Returns every children of every resource (includes the startnode).
     *
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode[] $nodes
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceNode[]
     *
     * @throws \Exception
     */
    public function expandResources(array $nodes)
    {
        $dirs = array();
        $ress = array();
        $toAppend = array();

        foreach ($nodes as $node) {
            $resourceTypeName = $node->getResourceType()->getName();
            ($resourceTypeName === 'directory') ? $dirs[] = $node : $ress[] = $node;
        }

        foreach ($dirs as $dir) {
            $children = $this->getDescendants($dir);

            foreach ($children as $child) {
                if ($child->getResourceType()->getName() !== 'directory') {
                    $toAppend[] = $child;
                }
            }
        }

        $merge = array_merge($toAppend, $ress);

        return $merge;
    }

    /**
     * Gets the relative path between 2 instances (not optimized yet).
     *
     * @param ResourceNode $root
     * @param ResourceNode $node
     * @param string       $path
     *
     * @return string
     */
    private function getRelativePath(ResourceNode $root, ResourceNode $node, $path = '')
    {
        if ($root !== $node->getParent() && $node->getParent() !== null) {
            $path = $node->getParent()->getName() . DIRECTORY_SEPARATOR . $path;
            $path = $this->getRelativePath($root, $node->getParent(), $path);
        }

        return $path;
    }

    /**
     * Renames a node.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $node
     * @param string                                             $name
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceNode
     */
    public function rename(ResourceNode $node, $name)
    {
        $node->setName($name);
        $name = $this->getUniqueName($node, $node->getParent());
        $node->setName($name);
        $this->om->persist($node);
        $this->logChangeSet($node);
        $this->om->flush();

        return $node;
    }

    /**
     * Changes a node icon.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode  $node
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceIcon
     */
    public function changeIcon(ResourceNode $node, UploadedFile $file)
    {
        $this->om->startFlushSuite();
        $icon = $this->iconManager->createCustomIcon($file, $node->getWorkspace());
        $this->iconManager->replace($node, $icon);
        $this->logChangeSet($node);
        $this->om->endFlushSuite();

        return $icon;
    }

    /**
     * Logs every change on a node.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $node
     */
    public function logChangeSet(ResourceNode $node)
    {
        $uow = $this->om->getUnitOfWork();
        $uow->computeChangeSets();
        $changeSet = $uow->getEntityChangeSet($node);

        if (count($changeSet) > 0) {
            $this->dispatcher->dispatch(
                'log',
                'Log\LogResourceUpdate',
                array($node, $changeSet)
            );
        }
    }

    /**
     * @param string $class
     * @param string $name
     *
     * @return \Claroline\CoreBundle\Entity\Resource\AbstractResource
     *
     * @throws WrongClassException
     */
    public function createResource($class, $name)
    {
        $entity = $this->om->factory($class);

        if ($entity instanceof \Claroline\CoreBundle\Entity\Resource\AbstractResource) {
            $entity->setName($name);

            return $entity;
        }

        throw new WrongClassException(
            "{$class} doesn't extend Claroline\CoreBundle\Entity\Resource\AbstractResource."
        );
    }

    /**
     * @param integer $id
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceNode
     */
    public function getNode($id)
    {
        return $this->resourceNodeRepo->find($id);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @return array
     */
    public function getRoots(User $user)
    {
        return $this->resourceNodeRepo->findWorkspaceRootsByUser($user);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     *
     * @return array
     */
    public function getWorkspaceRoot(Workspace $workspace)
    {
        return $this->resourceNodeRepo->findWorkspaceRoot($workspace);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $node
     *
     * @return array
     */
    public function getAncestors(ResourceNode $node)
    {
        return $this->resourceNodeRepo->findAncestors($node);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $node
     * @param string[]                                           $roles
     * @param boolean                                            $isSorted
     *
     * @return array
     */
    public function getChildren(
        ResourceNode $node,
        array $roles,
        User $user,
        $isSorted = true
    )
    {
        $children = $this->resourceNodeRepo->findChildren($node, $roles, $user);

        return ($isSorted) ? $this->sort($children, $user): $children;
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $node
     * @param boolean                                            $includeStartNode
     *
     * @return array
     */
    public function getAllChildren(ResourceNode $node, $includeStartNode)
    {
        return $this->resourceNodeRepo->getChildren($node, $includeStartNode, 'path', 'DESC');
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $node
     *
     * @return array
     */
    public function getDescendants(ResourceNode $node)
    {
        return $this->resourceNodeRepo->findDescendants($node);
    }

    /**
     * @param string                                             $mimeType
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $parent
     * @param string[]|RoleInterface[]                           $roles
     *
     * @return array
     */
    public function getByMimeTypeAndParent($mimeType, ResourceNode $parent, array $roles)
    {
        return $this->resourceNodeRepo->findByMimeTypeAndParent($mimeType, $parent, $roles);
    }

    /**
     * Find all the nodes wich mach the search criteria.
     * The search array must have the following structure (its array keys aren't required).
     *
     * array(
     *     'types' => array('typename1', 'typename2'),
     *     'roots' => array('rootpath1', 'rootpath2'),
     *     'dateFrom' => 'date',
     *     'dateTo' => 'date',
     *     'name' => 'name',
     *     'isExportable' => 'bool'
     * )
     *
     *
     * @param array                      $criteria
     * @param string[] | RoleInterface[] $userRoles
     * @param boolean                    $isRecursive
     *
     * @return array
     */
    public function getByCriteria(array $criteria, array $userRoles = null, $isRecursive = false)
    {
        return $this->resourceNodeRepo->findByCriteria($criteria, $userRoles, $isRecursive);
    }

    /**
     * @todo define the array content
     *
     * @param array $nodesIds
     *
     * @return array
     */
    public function getWorkspaceInfoByIds(array $nodesIds)
    {
        return $this->resourceNodeRepo->findWorkspaceInfoByIds($nodesIds);
    }

    /**
     * @param string $name
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceType
     */
    public function getResourceTypeByName($name)
    {
        return $this->resourceTypeRepo->findOneByName($name);
    }

    /**
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceType[]
     */
    public function getAllResourceTypes()
    {
        return $this->resourceTypeRepo->findAll();
    }

    /**
     * @param Workspace $workspace
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceNode[]
     */
    public function getByWorkspace(Workspace $workspace)
    {
        return $this->resourceNodeRepo->findBy(array('workspace' => $workspace));
    }

    /**
     * @param Workspace $workspace
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceNode[]
     */
    public function getByWorkspaceAndResourceType(
        Workspace $workspace,
        ResourceType $resourceType
    )
    {
        return $this->resourceNodeRepo->findBy(
            array('workspace' => $workspace, 'resourceType' => $resourceType),
            array('name' => 'ASC')
        );
    }

    /**
     * @param integer[] $ids
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceNode[]
     */
    public function getByIds(array $ids)
    {
        return $this->om->findByIds(
            'Claroline\CoreBundle\Entity\Resource\ResourceNode',
            $ids
        );
    }

    /**
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceNode
     */
    public function getById($id)
    {
        return $this->resourceNodeRepo->findOneby(array('id' => $id));
    }

    /**
     * Returns the resource linked to a node.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $node
     *
     * @return \Claroline\CoreBundle\Entity\Resource\AbstractResource
     */
    public function getResourceFromNode(ResourceNode $node)
    {
        return $this->om->getRepository($node->getClass())->findOneByResourceNode($node->getId());
    }

    /**
     * Copy a resource node.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $node
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $newParent
     * @param \Claroline\CoreBundle\Entity\User                  $user
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $last
     * @param boolean $withRights
     * Defines if the rights of the copied node have to be created
     * @param array $rights
     * If defined, the copied node will have exactly the given rights
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceNode
     */
    private function copyNode(
        ResourceNode $node,
        ResourceNode $newParent,
        User $user,
        ResourceNode $last = null,
        $withRights = true,
        array $rights = array()
    )
    {
        $newNode = $this->om->factory('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $newNode->setResourceType($node->getResourceType());
        $newNode->setCreator($user);
        $newNode->setWorkspace($newParent->getWorkspace());
        $newNode->setParent($newParent);
        $newParent->addChild($newNode);
        $newNode->setName($this->getUniqueName($node, $newParent, true));
        $newNode->setPrevious($last);
        $newNode->setNext(null);
        $newNode->setIcon($node->getIcon());
        $newNode->setClass($node->getClass());
        $newNode->setMimeType($node->getMimeType());
        $newNode->setAccessibleFrom($node->getAccessibleFrom());
        $newNode->setAccessibleUntil($node->getAccessibleUntil());
        $newNode->setPublished($node->isPublished());

        if ($withRights) {
            //if everything happens inside the same workspace and no specific rights have been given,
            //rights are copied
            if ($newParent->getWorkspace() === $node->getWorkspace() && count($rights) === 0) {
                $this->rightsManager->copy($node, $newNode);
            } else {
                //otherwise we use the parent rights or the given rights if not empty
                $this->setRights($newNode, $newParent, $rights);
            }
        }

        $this->om->persist($newNode);

        return $newNode;
    }

    /**
     * Set the previous and the next node of a nodes array to null.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode[] $nodes
     */
    private function resetNodeOrder($nodes)
    {
        foreach ($nodes as $node) {
            if (null !== $node) {
                $node->setPrevious(null);
                $node->setNext(null);
                $this->om->persist($node);
            }
        }

        $this->om->flush();
    }

    /**
     * required by insertBefore
     *
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $next
     */
    public function removeNextWhereNextIs(ResourceNode $next = null)
    {
        $node = $this->resourceNodeRepo->findOneBy(array('next' => $next));
        if ($node) {
            $node->setNext(null);
            $this->om->persist($node);
            $this->om->flush();
        }
    }

    /**
     * required by insertBefore
     *
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $previous
     */
    public function removePreviousWherePreviousIs(ResourceNode $previous = null)
    {
        $node = $this->resourceNodeRepo->findOneBy(array('previous' => $previous));
        if ($node) {
            $node->setPrevious(null);
            $this->om->persist($node);
            $this->om->flush();
        }
    }

    /**
     * Restore the order of each children of $parent. This may be usefull if the order is corrupted.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $parent
     */
    public function restoreNodeOrder(ResourceNode $parent)
    {
        $children = $parent->getChildren();
        $countChildren = count($children);
        $this->resetNodeOrder($children);
        $this->om->flush();

        for ($i = 0; $i < $countChildren; $i++) {
            if ($i === 0) {
                $children[$i]->setPrevious(null);
                $children[$i]->setNext($children[$i + 1]);
            } else {
                if ($i === $countChildren - 1) {
                    $children[$i]->setNext(null);
                    $children[$i]->setPrevious($children[$i - 1]);
                } else {
                    $children[$i]->setPrevious($children[$i - 1]);
                    $children[$i]->setNext($children[$i + 1]);
                }
            }
            $this->om->persist($children[$i]);
        }

        $this->om->flush();
    }

    private function getEncoding()
    {
        return $this->ut->getDefaultEncoding();
    }

    /**
     * Returns true of the token owns the workspace of the resource node.
     *
     * @param ResourceNode   $node
     * @param TokenInterface $token
     *
     * @return boolean
     */
    public function isWorkspaceOwnerOf(ResourceNode $node, TokenInterface $token)
    {
        $workspace = $node->getWorkspace();
        $managerRoleName = 'ROLE_WS_MANAGER_' . $workspace->getGuid();

        return in_array($managerRoleName, $this->secut->getRoles($token)) ? true: false;
    }

    public function resetIcon(ResourceNode $node)
    {
        $this->om->startFlushSuite();
        $icon = $this->iconManager->getIcon(
            $this->getResourceFromNode($node),
            $node->getWorkspace()
        );
        $node->setIcon($icon);
        $this->om->endFlushSuite();
    }

    /**
     * Retrieves all descendants of given ResourceNode and updates their
     * accessibility dates.
     *
     * @param ResourceNode $node A directory
     * @param datetime $accessibleFrom
     * @param datetime $accessibleUntil
     */
    public function changeAccessibilityDate(
        ResourceNode $node,
        $accessibleFrom,
        $accessibleUntil
    )
    {
        if ($node->getResourceType()->getName() === 'directory') {
            $descendants = $this->resourceNodeRepo->findDescendants($node);

            foreach ($descendants as $descendant) {
                $descendant->setAccessibleFrom($accessibleFrom);
                $descendant->setAccessibleUntil($accessibleUntil);
                $this->om->persist($descendant);
            }
            $this->om->flush();
        }
    }

    /**
     * Returns true if the listener is implemented for a resourceType and an action
     *
     * @param ResourceType $resourceType
     * @param string       $actionName
     */
    public function isResourceActionImplemented(ResourceType $resourceType, $actionName)
    {
        $alwaysTrue = array('rename', 'edit-properties', 'edit-rights', 'open-tracking');
        //first, directories can be downloaded even if there is no listener attached to it
        if ($resourceType->getName() === 'directory' && $actionName == 'download') return true;
        if (in_array($actionName, $alwaysTrue)) return true;


        return $this->dispatcher->hasListeners($actionName . '_' . $resourceType->getName());
    }

    private function isDirectoryEmpty($dirName)
    {
        $files = array ();
        $dirHandle = opendir($dirName);

        if ($dirHandle) {

            while ($file = readdir($dirHandle)) {

                if ($file !== '.' && $file !== '..') {
                    $files[] = $file;
                    break;
                }
            }
            closedir($dirHandle);
        }

        return count($files) === 0;
    }

    private function updateWorkspace(ResourceNode $node, Workspace $workspace)
    {
        $this->om->startFlushSuite();
        $node->setWorkspace($workspace);
        $this->om->persist($node);

        if ($node->getResourceType()->getName() === 'directory') {
            $children = $this->resourceNodeRepo->getChildren($node);

            foreach ($children as $child) {
                $child->setWorkspace($workspace);
                $this->om->persist($child);
            }
        }
        $this->om->endFlushSuite();
    }
}
