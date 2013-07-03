<?php

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\ResourceShortcut;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Repository\ResourceTypeRepository;
use Claroline\CoreBundle\Repository\AbstractResourceRepository;
use Claroline\CoreBundle\Repository\ResourceRightsRepository;
use Claroline\CoreBundle\Repository\ResourceShortcutRepository;
use Claroline\CoreBundle\Repository\RoleRepository;
use Claroline\CoreBundle\Manager\RightsManager;
use Claroline\CoreBundle\Manager\IconManager;
use Claroline\CoreBundle\Manager\Exception\MissingResourceNameException;
use Claroline\CoreBundle\Manager\Exception\ResourceTypeNotFoundException;
use Claroline\CoreBundle\Manager\Exception\RightsException;
use Claroline\CoreBundle\Library\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Database\Writer;
use Symfony\Component\EventDispatcher\EventDispatcher;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Library\Event\CopyResourceEvent;

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
    /** @var IconManager */
    private $iconManager;
    /** @var EventDispatcher */
    private $ed;
    /** @var Writer */
    private $writer;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "resourceTypeRepo" = @DI\Inject("resource_type_repository"),
     *     "resourceRepo" = @DI\Inject("resource_repository"),
     *     "resourceRightsRepo" = @DI\Inject("resource_rights_repository"),
     *     "roleRepo" = @DI\Inject("role_repository"),
     *     "shortcutRepo" = @DI\Inject("shortcut_repository"),
     *     "iconManager" = @DI\Inject("claroline.manager.icon_manager"),
     *     "rightsManager" = @DI\Inject("claroline.manager.rights_manager"),
     *     "ed" = @DI\Inject("event_dispatcher"),
         * "writer" = @DI\Inject("claroline.database.writer")
     * })
     */
    public function __construct (
        ResourceTypeRepository $resourceTypeRepo,
        AbstractResourceRepository $resourceRepo,
        ResourceRightsRepository $resourceRightsRepo,
        RoleRepository $roleRepo,
        ResourceShortcutRepository $shortcutRepo,
        IconManager $iconManager,
        RightsManager $rightsManager,
        EventDispatcher $ed,
        Writer $writer
    )
    {
        $this->resourceTypeRepo = $resourceTypeRepo;
        $this->resourceRepo = $resourceRepo;
        $this->resourceRightsRepo = $resourceRightsRepo;
        $this->roleRepo = $roleRepo;
        $this->shortcutRepo = $shortcutRepo;
        $this->iconManager = $iconManager;
        $this->rightsManager = $rightsManager;
        $this->ed = $ed;
        $this->writer = $writer;
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
        $icon = null,
        array $rights = array()
    )
    {
        $this->checkResourcePrepared($resource);
        $name = $this->getUniqueName($resource, $parent);
        $previous = $this->resourceRepo->findOneBy(array('parent' => $parent, 'next' => null));
        $icon = $this->generateIcon($resource, $resourceType, $icon);
        $resource->setCreator($creator);
        $resource->setWorkspace($workspace);
        $resource->setResourceType($resourceType);
        $resource->setParent($parent);
        $resource->setName($name);
        $resource->setPrevious($previous);
        $resource->setNext(null);
        $resource->setIcon($icon);
        $this->setRights($resource, $parent, $rights);
        $this->writer->create($resource);

        return $resource;
    }

    /**
     * Gets a unique name for a resource in a folder.
     * If the name of the resource already exists here, ~*indice* will be happened
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

    public function generateIcon(AbstractResource $resource, ResourceType $type, $icon = null)
    {
        if ($icon === null) {
            return $this->iconManager->findResourceIcon($resource, $type);
        } else {
            return $this->iconManager->createCustomIcon($icon);
        }
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

        return $this->create(
            $shortcut,
            $target->getResourceType(),
            $creator,
            $parent->getWorkspace(),
            $parent
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
     * @param \Claroline\CoreBundle\Entity\Resource\AbstractResource $resource
     * @param \Claroline\CoreBundle\Entity\Resource\AbstractResource $next
     */
    public function insertBefore(AbstractResource $resource, AbstractResource $next = null)
    {
        $previous = $this->findPreviousOrLastRes($next);
        $resource->setPrevious($previous);
        $resource->setNext($next);

        if ($next) {
            $next->setPrevious($resource);
        }

        if ($previous) {
            $previous->setNext($resource);
        }

        $this->writer->update($resource);
        $this->writer->update($next);
        $this->writer->update($previous);

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
            $this->writer->update($child);

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
        $lastChild = $this->resourceRepo->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
            ->findOneBy(array('parent' => $parent, 'next' => null));

        $resource->setPrevious($lastChild);
        $resource->setNext(null);
        $this->writer->update($resource);

        if ($lastChild) {
            $lastChild->setNext($resource);
            $this->writer->update($lastChild);
        }
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
            $this->writer->update($next);
        }

        if ($previous) {
            $previous->setNext($next);
            $this->writer->update($previous);
        }
    }

    public function findPreviousOrLastRes($resource = null)
    {
        return ($resource !== null) ?
            $resource->getPrevious():
            $this->resourceRepo->findOneBy(array('parent' => $resource->getParent(), 'next' => null));
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
            $copy = newResourceShortcut();
            $copy->setTarget($resource->getTarget());
            $copy->setCreator($user);
            $copy->setWorkspace($parent->getWorkspace());
            $copy->setResourceType($resource->getResourceType());
            $copy->setParent($parent);
            $copy->setName($this->getUniqueName($resource, $parent));
            $copy->setPrevious($last);
            $copy->setNext(null);
            $resource->setIcon($resource->getIcon());
        } else {
            $event = new CopyResourceEvent($resource);
            $eventName = 'copy_' . $resource->getResourceType()->getName();
            $this->ed->dispatch($eventName, $event);
            $copy = $event->getCopy();

            if ($copy === null) {
                throw new \Exception(
                    "The resource {$resource->getResourceType()->getName()}" .
                    " couldn't be created."
                );
            }

            $copy->setResourceType($resource->getResourceType());
            $copy->setCreator($user);
            $copy->setWorkspace($parent->getWorkspace());
            $copy->setResourceType($resource->getResourceType());
            $copy->setParent($parent);
            $copy->setName($this->getUniqueName($resource, $parent));
            $copy->setPrevious($last);
            $copy->setNext(null);

            if ($resource->getResourceType()->getName() == 'directory') {
                foreach ($resource->getChildren() as $child) {
                    $this->copy($child, $copy, $user);
                }
            }
        }

        if ($last) {
            $this->writer->setOrder($last, $last->getPrevious(), $resource);
        }

        return $copy;
    }

    /**
     * Removes a resource.
     *
     * @param AbstractResource $resource
     */
    public function delete(AbstractResource $resource)
    {
        $this->removePosition($resource);
        $eventName = 'delete_'.$resource->getResourceType()->getName();
        $event = new DeleteResourceEvent($resource);
        $this->ed->dispatch($eventName, $event);
        $this->writer->delete($resource);
    }

    /**
     * Generates a globally unique identifier.
     *
     * @see http://php.net/manual/fr/function.com-create-guid.php
     *
     * @return string
     */
    public function generateGuid()
    {
        if (function_exists('com_create_guid') === true) {
            return trim(com_create_guid(), '{}');
        }

        return sprintf(
            '%04X%04X-%04X-%04X-%04X-%04X%04X%04X',
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(16384, 20479),
            mt_rand(32768, 49151),
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(0, 65535)
        );
    }
}