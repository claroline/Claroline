<?php

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Resource\ResourceShortcut;
use Claroline\CoreBundle\Entity\Resource\ResourceIcon;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Repository\ResourceTypeRepository;
use Claroline\CoreBundle\Repository\ResourceNodeRepository;
use Claroline\CoreBundle\Repository\ResourceRightsRepository;
use Claroline\CoreBundle\Repository\ResourceShortcutRepository;
use Claroline\CoreBundle\Repository\RoleRepository;
use Claroline\CoreBundle\Manager\RightsManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\IconManager;
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
use JMS\DiExtraBundle\Annotation as DI;

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
    /** @var IconManager */
    private $iconManager;
    /** @var Dispatcher */
    private $dispatcher;
    /** @var ObjectManager */
    private $om;
    /** @var ClaroUtilities */
    private $ut;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "roleManager"   = @DI\Inject("claroline.manager.role_manager"),
     *     "iconManager"   = @DI\Inject("claroline.manager.icon_manager"),
     *     "rightsManager" = @DI\Inject("claroline.manager.rights_manager"),
     *     "dispatcher"    = @DI\Inject("claroline.event.event_dispatcher"),
     *     "om"            = @DI\Inject("claroline.persistence.object_manager"),
     *     "ut"            = @DI\Inject("claroline.utilities.misc")
     * })
     */
    public function __construct (
        RoleManager $roleManager,
        IconManager $iconManager,
        RightsManager $rightsManager,
        StrictDispatcher $dispatcher,
        ObjectManager $om,
        ClaroUtilities $ut
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
        $this->dispatcher = $dispatcher;
        $this->om = $om;
        $this->ut = $ut;
    }

    /**
     * array $rights should be defined that way:
     * array('ROLE_WS_XXX' => array('open' => true, 'edit' => false, ...
     * 'create' => array('directory', ...), role => $entity))
     *
     */
    public function create(
        AbstractResource $resource,
        ResourceType $resourceType,
        User $creator,
        AbstractWorkspace $workspace,
        ResourceNode $parent = null,
        ResourceIcon $icon = null,
        array $rights = array()
    )
    {
        $this->om->startFlushSuite();
        $this->checkResourcePrepared($resource);
        $node = $this->om->factory('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $node->setResourceType($resourceType);

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
        $resource->setResourceNode($node);
        $this->setRights($node, $parent, $rights);
        $this->om->persist($node);
        $this->om->persist($resource);

        if ($icon === null) {
            $icon = $this->iconManager->getIcon($resource);
        }

        $parentPath = '';

        if ($parent) {
            $parentPath .= $parent->getPathForDisplay() . ' / ';
        }

        $node->setPathForCreationLog($parentPath . $name);
        $node->setIcon($icon);
        $this->dispatcher->dispatch('log', 'Log\LogResourceCreate', array($node));
        $this->om->endFlushSuite();

        return $resource;
    }

    /**
     * Gets a unique name for a resource in a folder.
     * If the name of the resource already exists here, ~*indice* will be happended
     * to its name
     *
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $resource
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $parent
     *
     * @return string
     */
    public function getUniqueName(ResourceNode $node, ResourceNode $parent = null)
    {
        $children = $this->getSiblings($parent);
        $name = $node->getName();
        $arName = explode('~', pathinfo($name, PATHINFO_FILENAME));
        $baseName = $arName[0];
        $nbName = 0;

        if ($children) {
            foreach ($children as $child) {
                $arChildName = explode('~', pathinfo($child->getName(), PATHINFO_FILENAME));
                if ($baseName === $arChildName[0]) {
                    $nbName++;
                }
            }
        }

        return (0 !== $nbName) ?  $baseName.'~'.$nbName.'.'.pathinfo($name, PATHINFO_EXTENSION): $name;
    }

    public function getSiblings(ResourceNode $parent = null)
    {
        if ($parent !== null) {
            return $parent->getChildren();
        }

        return $this->resourceNodeRepo->findBy(array('parent' => null));
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
    public function findAndSortChildren(ResourceNode $parent)
    {
        //a little bit hacky but retrieve all children of the parent
        $nodes = $this->resourceNodeRepo->findChildren($parent, array('ROLE_ADMIN'));
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
                throw new \Exception('More than 100 items in a directory or infinite loop detected. The order was reseted');
            }
        }

        return $sorted;
    }

    /**
     * Sort an array of serialized resources. The chained list can have some "holes".
     *
     * @param array $nodes
     *
     * @return array
     */
    public function sort(array $nodes)
    {
        $sortedResources = array();

        if (count($nodes) > 0) {
            if ($this->haveSameParents($nodes)) {
                $parent = $this->resourceNodeRepo->find($nodes[0]['parent_id']);
                $sortedList = $this->findAndSortChildren($parent);

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
     * @todo
     * Define the $rights array.
     * If there is no rights: parents rights are copied.
     * Otherwise: use the new rights array;
     */
    public function setRights(
        ResourceNode $node,
        ResourceNode $parent = null,
        array $rights = array()
    )
    {
        if (count($rights) === 0 && $parent !== null) {
            $this->rightsManager->copy($parent, $node);
        } else {
            if (count($rights) === 0) {
                throw new RightsException('Rights must be specified if there is no parent');
            }
            $this->createRights($node, $rights);
        }
    }

    /*
     * Creates the base rights for a resource
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

        $resourceTypes = $this->resourceTypeRepo->findAll();

        //@todo remove this line and grant edit requests in the resourceManager.
        $this->rightsManager->create(
            31,
            $this->roleRepo->findOneBy(array('name' => 'ROLE_ADMIN')),
            $node,
            false,
            $resourceTypes
        );

        $this->rightsManager->create(
            0,
            $this->roleRepo->findOneBy(array('name' => 'ROLE_ANONYMOUS')),
            $node,
            false,
            array()
        );
    }

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

    //expects an array of types array(array('name' => 'type'),...);
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
     * @param ResourceNode $resource
     * @param ResourceNode $next
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
     * @param ResourceNode $child
     * @param ResourceNode $parent
     */
    public function move(ResourceNode $child, ResourceNode $parent)
    {
        $this->removePosition($child);
        $this->setLastPosition($parent, $child);

        if ($parent === $child) {
            throw new ResourceMoveException("You cannot move a directory into itself");
        }

        $child->setParent($parent);
        $child->setName($this->getUniqueName($child, $parent));
        $this->om->persist($child);
        $this->om->flush();
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

        if ($next) {
            $next->setPrevious($previous);
            $this->om->persist($next);
        }

        if ($previous) {
            $previous->setNext($next);
            $this->om->persist($previous);
        }

        $this->om->flush();
    }

    public function findPreviousOrLastRes(ResourceNode $parent, ResourceNode $node = null)
    {
        return ($node !== null) ?
            $node->getPrevious():
            $this->resourceNodeRepo->findOneBy(array('parent' => $parent, 'next' => null));
    }

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
     * Copies a resource in a directory.
     *
     * @param ResourceNode $node
     * @param ResourceNode $parent
     */
    public function copy(ResourceNode $node, ResourceNode $parent, User $user)
    {
        $last = $this->resourceNodeRepo->findOneBy(array('parent' => $parent, 'next' => null));
        $resource = $this->getResourceFromNode($node);

        if ($resource instanceof \Claroline\CoreBundle\Entity\Resource\ResourceShortcut) {
            $copy = $this->om->factory('Claroline\CoreBundle\Entity\Resource\ResourceShortcut');
            $copy->setTarget($resource->getTarget());
            $newNode = $this->copyNode($node, $parent, $user, $last);
            $copy->setResourceNode($newNode);

        } else {
            $event = $this->dispatcher->dispatch(
                'copy_' . $node->getResourceType()->getName(),
                'CopyResource',
                array($resource)
            );

            $copy = $event->getCopy();
            $newNode = $this->copyNode($node, $parent, $user, $last);
            $copy->setResourceNode($newNode);

            if ($node->getResourceType()->getName() == 'directory') {
                foreach ($node->getChildren() as $child) {
                    $this->copy($child, $newNode, $user);
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
     * Convert a ressource into an array (mainly used to be serialized and sent to the manager.js as
     * a json response)
     *
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $node
     *
     * @return array
     */
    public function toArray(ResourceNode $node)
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

        if ($node->getPrevious() !== null) {
            $resourceArray['previous_id'] = $node->getPrevious()->getId();
        }
        if ($node->getNext() !== null) {
            $resourceArray['next_id'] = $node->getNext()->getId();
        }

        $isAdmin = false;

        $roles = $this->roleManager->getStringRolesFromCurrentUser();

        foreach ($roles as $role) {
            if ($role === 'ROLE_ADMIN') {
                $isAdmin = true;
            }
        }

        if ($isAdmin) {
            $resourceArray['mask'] = 1023;
        } else {
            $resourceArray['mask'] = $this->resourceRightsRepo->findMaximumRights($roles, $node);
        }

        return $resourceArray;
    }

    /**
     * Removes a resource.
     *
     * @param ResourceNode $resource
     */
    public function delete(ResourceNode $node)
    {
        //why is it broken when this function is fired after startFlushSuite ?
        $this->removePosition($node);
        $this->om->startFlushSuite();
        $this->dispatcher->dispatch(
            "delete_{$node->getResourceType()->getName()}",
            'DeleteResource',
            array($this->getResourceFromNode($node))
        );
        $this->om->remove($node);
        $this->dispatcher->dispatch('log', 'Log\LogResourceDelete', array($node));
        $this->om->endFlushSuite();
    }

    /**
     * Returns an archive with the required content.
     *
     * @param array $nodes the nodes being exported
     *
     * @return file
     */
    public function download(array $nodes)
    {
        if (count($nodes) === 0) {
            throw new ExportResourceException('No resources were selected.');
        }

        $archive = new \ZipArchive();
        $pathArch = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $this->ut->generateGuid() . '.zip';
        $archive->open($pathArch, \ZipArchive::CREATE);
        $nodes = $this->expandResources($nodes);

        $currentDir = $nodes[0];

        foreach ($nodes as $node) {

            $resource = $this->getResourceFromNode($node);

            if (get_class($resource) === 'Claroline\CoreBundle\Entity\Resource\ResourceShortcut') {
                $node = $resource->getTarget()->getResourceNode();
            }

            if ($node->getResourceType()->getName() !== 'directory') {
                $event = $this->dispatcher->dispatch(
                    "download_{$node->getResourceType()->getName()}",
                    'DownloadResource',
                    array($this->getResourceFromNode($node))
                );

                $obj = $event->getItem();

                if ($obj !== null) {
                    $archive->addFile($obj, $this->getRelativePath($currentDir, $node) . $node->getName());
                } else {
                     $archive->addFromString($this->getRelativePath($currentDir, $node) . $node->getName(), '');
                }
            } else {
                $archive->addEmptyDir($this->getRelativePath($currentDir, $node). $node->getName());
            }

            $this->dispatcher->dispatch('log', 'Log\LogResourceExport', array($node));
        }

        $archive->close();

        return $pathArch;
    }

    /**
     * Returns every children of every resource (includes the startnode).
     *
     * @param  array      $nodes
     * @return type
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
        $merge = array_merge($merge, $dirs);

        return $merge;
    }

    /**
     * Gets the relative path between 2 instances (not optimized yet).
     *
     * @param ResourceNode $root
     * @param ResourceNode $resourceInstance
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

    public function rename(ResourceNode $node, $name)
    {
        $node->setName($name);
        $this->om->persist($node);
        $this->logChangeSet($node);
        $this->om->flush();

        return $node;
    }

    public function changeIcon(ResourceNode $node, UploadedFile $file)
    {
        $this->om->startFlushSuite();
        $icon = $this->iconManager->createCustomIcon($file);
        $this->iconManager->replace($node, $icon);
        $this->logChangeSet($node);
        $this->om->endFlushSuite();

        return $icon;
    }

    public function logChangeSet(ResourceNode $node)
    {
        $uow = $this->om->getUnitOfWork();
        $uow->computeChangeSets();
        $changeSet = $uow->getEntityChangeSet($node);

        if (count($changeSet > 0)) {
            $this->dispatcher->dispatch(
                'log',
                'Log\LogResourceUpdate',
                array($node, $changeSet)
            );
        }
    }

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

    public function getNode($id)
    {
        return $this->resourceNodeRepo->find($id);
    }

    public function getRoots(User $user)
    {
        return $this->resourceNodeRepo->findWorkspaceRootsByUser($user);
    }

    public function getWorkspaceRoot(AbstractWorkspace $workspace)
    {
        return $this->resourceNodeRepo->findWorkspaceRoot($workspace);
    }

    public function getAncestors(ResourceNode $node)
    {
        return $this->resourceNodeRepo->findAncestors($node);
    }

    public function getChildren(ResourceNode $node, array $roles, $isSorted = true)
    {
        $children = $this->resourceNodeRepo->findChildren($node, $roles);

        return ($isSorted) ? $this->sort($children): $children;
    }

    public function getAllChildren(ResourceNode $node, $includeStartNode)
    {
        return $this->resourceNodeRepo->getChildren($node, $includeStartNode, 'path', 'DESC');
    }

    public function getDescendants(ResourceNode $node)
    {
        return $this->resourceNodeRepo->findDescendants($node);
    }

    public function getByCriteria(array $criteria, array $userRoles = null, $isRecursive = false)
    {
        return $this->resourceNodeRepo->findByCriteria($criteria, $userRoles, $isRecursive);
    }

    public function getResourceTypeByName($name)
    {
        return $this->resourceTypeRepo->findOneByName($name);
    }

    public function getAllResourceTypes()
    {
        return $this->resourceTypeRepo->findAll();
    }

    public function getByIds(array $ids)
    {
        return $this->om->findByIds(
            'Claroline\CoreBundle\Entity\Resource\ResourceNode',
            $ids
        );
    }

    public function getResourceFromNode(ResourceNode $node)
    {
        return $this->om->getRepository($node->getClass())->findOneByResourceNode($node->getId());
    }

    private function copyNode(ResourceNode $node, ResourceNode $newParent, User $user,  ResourceNode $last = null)
    {
        $newNode = $this->om->factory('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $newNode->setResourceType($node->getResourceType());
        $newNode->setCreator($user);
        $newNode->setWorkspace($newParent->getWorkspace());
        $newNode->setParent($newParent);
        $newNode->setName($this->getUniqueName($node, $newParent));
        $newNode->setPrevious($last);
        $newNode->setNext(null);
        $newNode->setIcon($node->getIcon());
        $newNode->setClass($node->getClass());
        $this->rightsManager->copy($node, $newNode);
        $this->om->persist($newNode);

        return $newNode;
    }

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

    /** required by insertBefore */
    public function removeNextWhereNextIs(ResourceNode $next = null)
    {
        $node = $this->resourceNodeRepo->findOneBy(array('next' => $next));
        if ($node) {
            $node->setNext(null);
            $this->om->persist($node);
            $this->om->flush();
        }
    }

    /** required by insertBefore */
    public function removePreviousWherePreviousIs(ResourceNode $previous = null)
    {
        $node = $this->resourceNodeRepo->findOneBy(array('previous' => $previous));
        if ($node) {
            $node->setPrevious(null);
            $this->om->persist($node);
            $this->om->flush();
        }
    }

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
}
