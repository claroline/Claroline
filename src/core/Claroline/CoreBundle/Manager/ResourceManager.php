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
use Claroline\CoreBundle\Repository\RoleRepository;
use Claroline\CoreBundle\Writer\ResourceWriter;
use Claroline\CoreBundle\Manager\RightsManager;
use Claroline\CoreBundle\Manager\IconManager;
use Claroline\CoreBundle\Manager\Exception\MissingResourceNameException;
use Claroline\CoreBundle\Manager\Exception\ResourceTypeNotFoundException;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.resource_manager")
 */
class ResourceManager
{
    /** @var ResourceWriter */
    private $writer;
    /** @var RightsManager */
    private $rightsManager;
    /** @var ResourceTypeRepository */
    private $resourceTypeRepo;
    /** @var AbstractResourceRepository */
    private $resourceRepo;
    /** @var ResourceRightsRepository */
    private $resourceRightsRepo;
    /** @var RoleRepository */
    private $roleRepo;
    /** @var IconManager */
    private $iconManager;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "resourceTypeRepo" = @DI\Inject("resource_type_repository"),
     *     "resourceRepo" = @DI\Inject("resource_repository"),
     *     "resourceRightsRepo" = @DI\Inject("resource_rights_repository"),
     *     "roleRepo" = @DI\Inject("role_repository"),
     *     "iconManager" = @DI\Inject("claroline.manager.icon_manager"),
     *     "writer" = @DI\Inject("claroline.writer.resource_writer"),
     *     "rightsManager" = @DI\Inject("claroline.manager.rights_manager")
     * })
     */
    public function __construct (
        ResourceTypeRepository $resourceTypeRepo,
        AbstractResourceRepository $resourceRepo,
        ResourceRightsRepository $resourceRightsRepo,
        RoleRepository $roleRepo,
        IconManager $iconManager,
        ResourceWriter $writer,
        RightsManager $rightsManager
    )
    {
        $this->resourceTypeRepo = $resourceTypeRepo;
        $this->resourceRepo = $resourceRepo;
        $this->resourceRightsRepo = $resourceRightsRepo;
        $this->roleRepo = $roleRepo;
        $this->iconManager = $iconManager;
        $this->writer = $writer;
        $this->rightsManager = $rightsManager;
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
        $entityIcon = $this->generateIcon($resource, $resourceType, $icon);
        $resource = $this->writer->create(
            $resource,
            $resourceType,
            $creator,
            $workspace,
            $name,
            $entityIcon,
            $parent,
            $previous
        );

        $this->setRights($resource, $parent, $rights);

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
                throw new \Exception('Rights must be specified if there is no parent');
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
}