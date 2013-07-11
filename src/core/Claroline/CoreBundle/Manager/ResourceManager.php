<?php

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\ResourceShortcut;
use Claroline\CoreBundle\Entity\Resource\ResourceIcon;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Repository\ResourceTypeRepository;
use Claroline\CoreBundle\Repository\AbstractResourceRepository;
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
    /** @var AbstractResourceRepository */
    private $resourceRepo;
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
        $this->resourceRepo = $om->getRepository('ClarolineCoreBundle:Resource\AbstractResource');
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
     * define the array rights
     */
    public function create(
        AbstractResource $resource,
        ResourceType $resourceType,
        User $creator,
        AbstractWorkspace $workspace,
        AbstractResource $parent = null,
        ResourceIcon $icon = null,
        array $rights = array()
    )
    {
        $this->om->startFlushSuite();
        $this->checkResourcePrepared($resource);
        $name = $this->getUniqueName($resource, $parent);
        
        if ($resource->getMimeType() === null) {
            $resource->setMimeType('custom/' . $resourceType->getName());
        }
        
        $previous = $this->resourceRepo->findOneBy(array('parent' => $parent, 'next' => null));
        
        if ($previous) {
            $previous->setNext($resource);
        }
        if ($icon === null) {
            $icon = $this->iconManager->getIcon($resource, $icon);
        }
        
        $resource->setCreator($creator);
        $resource->setWorkspace($workspace);
        $resource->setResourceType($resourceType);
        $resource->setParent($parent);
        $resource->setName($name);
        $resource->setPrevious($previous);
        $resource->setNext(null);
        $resource->setIcon($icon);
        $this->setRights($resource, $parent, $rights);
        $this->om->persist($resource);
        //$this->dispatcher->dispatch('log', 'Log\ResourceCreateEvent', array($resource));
        $this->om->endFlushSuite();

        return $resource;
    }

    /**
     * Gets a unique name for a resource in a folder.
     * If the name of the resource already exists here, ~*indice* will be happended
     * to its name
     *
     * @param \Claroline\CoreBundle\Entity\Resource\AbstractResource $resource
     * @param \Claroline\CoreBundle\Entity\Resource\AbstractResource $parent
     *
     * @return string
     */
    public function getUniqueName(AbstractResource $resource, AbstractResource $parent = null)
    {
        $children = $this->getSiblings($parent);
        $name = $resource->getName();
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

    public function getSiblings(AbstractResource $parent = null)
    {
        if ($parent !== null) {
            return $parent->getChildren();
        }

        return $this->resourceRepo->findBy(array('parent' => null));
    }

    /**
     * Checks if an array of serialized resources share the same parent.
     *
     * @param array $resources
     *
     * @return array
     */
    public function haveSameParents(array $resources)
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
        $sortedResources = array();

        if (count($resources) > 0) {
            if ($this->haveSameParents($resources)) {
                $parent = $this->resourceRepo->find($resources[0]['parent_id']);
                $sortedList = $this->findAndSortChildren($parent);

                foreach ($sortedList as $sortedItem) {
                    foreach ($resources as $resource) {
                        if ($resource['id'] === $sortedItem['id']) {
                            $sortedResources[] = $resource;
                        }
                    }
                }
            } else {
                throw new \Exception("These resources don't share the same parent");
            }
        }

        return $sortedResources;
    }

    public function makeShortcut(AbstractResource $target, Directory $parent, User $creator, ResourceShortcut $shortcut)
    {
        $shortcut->setName($target->getName());

        if (get_class($target) !== 'Claroline\CoreBundle\Entity\Resource\ResourceShortcut') {
            $shortcut->setResource($target);
        } else {
            $shortcut->setResource($target->getResource());
        }

        //$this->dispatcher->dispatch('log', 'Log\ResourceCreateEvent', array($shortcut));
        
        return $this->create(
            $shortcut,
            $target->getResourceType(),
            $creator,
            $parent->getWorkspace(),
            $parent,
            $target->getIcon()->getShortcutIcon()
        );
    }


    /**
     * @todo
     * Define the $rights array.
     * If there is no rights: parents rights are copied.
     * Otherwise: use the new rights array;
     */
    public function setRights(
        AbstractResource $resource,
        AbstractResource $parent = null,
        array $rights = array()
    )
    {
        if (count($rights) === 0 && $parent !== null) {
            $this->rightsManager->copy($parent, $resource);
        } else {
            if (count($rights) === 0) {
                throw new RightsException('Rights must be specified if there is no parent');
            }
            $this->createRights($resource, $rights);
        }
    }

    /*
     * Creates the base rights for a resource
     */
    public function createRights(
        AbstractResource $resource,
        array $rights = array()
    )
    {
        foreach ($rights as $data) {
            $resourceTypes = $this->checkResourceTypes($data['canCreate']);
            $this->rightsManager->create($data, $data['role'], $resource, false, $resourceTypes);
        }

        $resourceTypes = $this->resourceTypeRepo->findAll();

        $this->rightsManager->create(
             array(
                'canDelete' => true,
                'canOpen' => true,
                'canEdit' => true,
                'canCopy' => true,
                'canExport' => true,
            ),
            $this->roleRepo->findOneBy(array('name' => 'ROLE_ADMIN')),
            $resource,
            false,
            $resourceTypes

        );

        $this->rightsManager->create(
             array(
                'canDelete' => false,
                'canOpen' => false,
                'canEdit' => false,
                'canCopy' => false,
                'canExport' => false,
            ),
            $this->roleRepo->findOneBy(array('name' => 'ROLE_ANONYMOUS')),
            $resource,
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
     * @param AbstractResource $resource
     * @param AbstractResource $next
     */
    public function insertBefore(AbstractResource $resource, AbstractResource $next = null)
    {
        $previous = $this->findPreviousOrLastRes($resource->getParent(), $next);
        $oldPrev = $resource->getPrevious();
        $oldNext = $resource->getNext();
        $resource->setPrevious($previous);
        $resource->setNext($next);

        if ($next) {
            $next->setPrevious($resource);
            $this->om->persist($next);
        }

        if ($previous) {
            $previous->setNext($resource);
            $this->om->persist($previous);
        }

        if ($oldPrev) {
            $oldPrev->setNext($oldNext);
            $this->om->persist($oldPrev);
        }

        if ($oldNext) {
            $oldNext->setPrevious($oldPrev);
            $this->om->persist($oldNext);
        }

        $this->om->flush();

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

        try {

            $child->setParent($parent);
            $child->setName($this->getUniqueName($child, $parent));
            $this->om->persist($child);
            $this->om->flush();
            //$this->dispatcher->dispatch('log', 'Log\ResourceMoveEvent', array($child, $parent));

            return $child;
        } catch (UnexpectedValueException $e) {
            throw new \UnexpectedValueException("You cannot move a directory into itself");
        }
    }


    /**
     * Set the $resource at the last position of the $parent.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\AbstractResource $parent
     * @param \Claroline\CoreBundle\Entity\Resource\AbstractResource $resource
     */
    public function setLastPosition(AbstractResource $parent, AbstractResource $resource)
    {
        $lastChild = $this->resourceRepo->findOneBy(array('parent' => $parent, 'next' => null));

        $resource->setPrevious($lastChild);
        $resource->setNext(null);
        $this->om->persist($resource);

        if ($lastChild) {
            $lastChild->setNext($resource);
            $this->om->persist($lastChild);
        }
        
        $this->om->flush();
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

    public function findPreviousOrLastRes(AbstractResource $parent, AbstractResource $resource = null)
    {
        return ($resource !== null) ?
            $resource->getPrevious():
            $this->resourceRepo->findOneBy(array('parent' => $parent, 'next' => null));
    }

    public function hasLinkTo(Directory $parent, Directory $target)
    {
        $shortcuts = $this->shortcutRepo->findBy(array('parent' => $parent));

        foreach ($shortcuts as $shortcut) {
            if ($shortcut->getResource() == $target) {
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

    public function areAncestorsDirectory(array $ancestors) {
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
     * @param AbstractResource $resource
     * @param AbstractResource $parent
     */
    public function copy(AbstractResource $resource, AbstractResource $parent, User $user)
    {
        $last = $this->resourceRepo->findOneBy(array('parent' => $parent, 'next' => null));

        if (get_class($resource) == 'Claroline\CoreBundle\Entity\Resource\ResourceShortcut') {
            $copy = new ResourceShortcut();
            $copy->setResource($resource->getResource());
            $copy->setCreator($user);
            $copy->setWorkspace($parent->getWorkspace());
            $copy->setResourceType($resource->getResourceType());
            $copy->setParent($parent);
            $copy->setName($this->getUniqueName($resource, $parent));
            $copy->setPrevious($last);
            $copy->setNext(null);
            $copy->setIcon($resource->getIcon());
            $this->rightsManager->copy($resource, $copy);
        } else {
            $event = $this->dispatcher->dispatch(
                'copy_' . $resource->getResourceType()->getName(),
                'CopyResource',
                array($resource)
            );

            $copy = $event->getCopy();
            $copy->setResourceType($resource->getResourceType());
            $copy->setCreator($user);
            $copy->setWorkspace($parent->getWorkspace());
            $copy->setResourceType($resource->getResourceType());
            $copy->setParent($parent);
            $copy->setName($this->getUniqueName($resource, $parent));
            $copy->setPrevious($last);
            $copy->setNext(null);
            $copy->setIcon($resource->getIcon());
            $this->rightsManager->copy($resource, $copy);

            if ($resource->getResourceType()->getName() == 'directory') {
                foreach ($resource->getChildren() as $child) {
                    $this->copy($child, $copy, $user);
                }
            }
        }

        $this->om->persist($copy);

        if ($last) {
            $last->setNext($copy);
            $this->om->persist($last);
        }

        //$this->dispatcher->dispatch('log', 'Log\ResourceCopyEvent', array($copy, $resource));
        $this->om->flush();
        
        return $copy;
    }

    public function getResourceTypeByName($name)
    {
        return $this->resourceTypeRepo->findOneByName($name);
    }

    /**
     * Convert a ressource into an array (mainly used to be serialized and sent to the manager.js as
     * a json response)
     *
     * @param \Claroline\CoreBundle\Entity\Resource\AbstractResource $resource
     *
     * @return array
     */
    public function toArray(AbstractResource $resource)
    {
        $resourceArray = array();
        $resourceArray['id'] = $resource->getId();
        $resourceArray['name'] = $resource->getName();
        $resourceArray['parent_id'] = ($resource->getParent() != null) ? $resource->getParent()->getId() : null;
        $resourceArray['creator_username'] = $resource->getCreator()->getUsername();
        $resourceArray['type'] = $resource->getResourceType()->getName();
        $resourceArray['large_icon'] = $resource->getIcon()->getRelativeUrl();
        $resourceArray['path_for_display'] = $resource->getPathForDisplay();
        $resourceArray['mime_type'] = $resource->getMimeType();

        if ($resource->getPrevious() !== null) {
            $resourceArray['previous_id'] = $resource->getPrevious()->getId();
        }
        if ($resource->getNext() !== null) {
            $resourceArray['next_id'] = $resource->getNext()->getId();
        }

        $isAdmin = false;

        $roles = $this->roleManager->getStringRolesFromCurrentUser();

        foreach ($roles as $role) {
            if ($role === 'ROLE_ADMIN') {
                $isAdmin = true;
            }
        }

        if ($isAdmin) {
            $resourceArray['can_export'] = true;
            $resourceArray['can_edit'] = true;
            $resourceArray['can_delete'] = true;
        } else {
            $rights = $this->resourceRightsRepo->findMaximumRights($roles, $resource);
            $resourceArray['can_export'] = $rights['canExport'];
            $resourceArray['can_edit'] = $rights['canEdit'];
            $resourceArray['can_delete'] = $rights['canDelete'];
        }

        return $resourceArray;
    }

    public function getRoots(User $user)
    {
        return $this->resourceRepo->findWorkspaceRootsByUser($user);
    }

    public function getWorkspaceRoot(AbstractWorkspace $workspace)
    {
        return $this->resourceRepo->findWorkspaceRoot($workspace);
    }

    public function getAncestors(AbstractResource $resource)
    {
        return $this->resourceRepo->findAncestors($resource);
    }

    public function getChildren(Directory $directory, array $roles, $isSorted = true)
    {
        $children = $this->resourceRepo->findChildren($directory, $roles);

        return ($isSorted) ? $this->sort($children): $children;
    }

    public function getDescendants(Directory $directory)
    {
        return $this->resourceRepo->findDescendants($directory);
    }

    public function getByCriteria(array $criteria, array $userRoles, $isRecursive)
    {
        return $this->resourceRepo->findByCriteria($criteria, $userRoles, $isRecursive);
    }

    public function getByIds(array $ids)
    {
        return $this->om->findByIds(
            'Claroline\CoreBundle\Entity\Resource\AbstractResource',
            $ids
        );
    }

    /**
     * Removes a resource.
     *
     * @param AbstractResource $resource
     */
    public function delete(AbstractResource $resource)
    {
        $this->om->startFlushSuite();
        $this->removePosition($resource);
        $this->dispatcher->dispatch('delete_'.$resource->getResourceType()->getName(), 'DeleteResource', array($resource));
        $this->om->remove($resource);
        //$this->dispatcher->dispatch('log', 'Log\ResourceDeleteEvent', array($resource));
        $this->om->endFlushSuite();
    }

    /**
     * Returns an archive with the required content.
     *
     * @param array $resources the resources being exported
     *
     * @return file
     */
    public function download(array $resources)
    {
        if (count($resources) === 0) {
            throw new ExportResourceException('No resources were selected.');
        }

        $archive = new \ZipArchive();
        $pathArch = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $this->ut->generateGuid() . '.zip';
        $archive->open($pathArch, \ZipArchive::CREATE);
        $resources = $this->expandResources($resources);

        $currentDir = $resources[0];

        foreach ($resources as $resource) {

            if (get_class($resource) === 'Claroline\CoreBundle\Entity\Resource\ResourceShortcut') {
                $resource = $resource->getResource();
            }

            if ($resource->getResourceType()->getName() !== 'directory') {
                $event = $this->dispatcher->dispatch(
                    "download_{$resource->getResourceType()->getName()}",
                    'DownloadResource',
                    array($resource)
                );

                $obj = $event->getItem();

                if ($obj !== null) {
                    $archive->addFile($obj, $this->getRelativePath($currentDir, $resource) . $resource->getName());
                } else {
                     $archive->addFromString($this->getRelativePath($currentDir, $resource) . $resource->getName(), '');
                }
            } else {
                $archive->addEmptyDir($this->getRelativePath($currentDir, $resource). $resource->getName());
            }

            $this->dispatcher->dispatch('log', 'Log\LogResourceExport', array($resource));
        }

        $archive->close();

        return $pathArch;
    }

    /**
     * Returns every children of every resource (includes the startnode).
     *
     * @param array $resources
     * @return type
     * @throws \Exception
     */
    public function expandResources(array $resources)
    {
        $dirs = array();
        $ress = array();

        foreach ($resources as $resource) {
            $resourceTypeName = $resource->getResourceType()->getName();
            ($resourceTypeName === 'directory') ? $dirs[] = $resource : $ress[] = $resource;
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
     * @param ResourceInstance $root
     * @param ResourceInstance $resourceInstance
     * @param string           $path
     *
     * @return string
     */
    private function getRelativePath($root, AbstractResource $resource, $path = '')
    {
        if ($root !== $resource->getParent() && $resource->getParent() !== null) {
            $path = $resource->getParent()->getName() . DIRECTORY_SEPARATOR . $path;
            $path = $this->getRelativePath($root, $resource->getParent(), $path);
        }

        return $path;
    }
    
    public function rename(AbstractResource $resource, $name)
    {
        $resource->setName($name);
        $this->om->persist($resource);
        $this->om->flush();
        
        return $resource;
    }
    
    public function changeIcon(AbstractResource $resource, UploadedFile $file)
    {
        $icon = $this->iconManager->createCustomIcon($file);
        $this->iconManager->replace($resource, $icon);
        
        return $icon;
    }
}