<?php

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Repository\ResourceTypeRepository;
use Claroline\CoreBundle\Repository\AbstractResourceRepository;
use Claroline\CoreBundle\Repository\ResourceRightsRepository;
use Claroline\CoreBundle\Writer\ResourceWriter;
use Claroline\CoreBundle\Manager\RightsManager;
use Claroline\CoreBundle\Manager\IconManager;
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
    /** @var AbstractResourceRightsRepository */
    private $resourceRightsRepo;
    /** @var IconManager */
    private $iconManager;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "resourceTypeRepo" = @Di\Inject("resource_type_repository"),
     *     "resourceRepo" = @Di\Inject("resource_repository"),
     *     "resourceRightsRepo" = @Di\Inject("resource_rights_repository"),
     *     "iconManager" = @Di\Inject("claroline.manager.icon_manager"),
     *     "writer" = @DI\Inject("claroline.writer.resource_writer"),
     *     "rightsManager" = @DI\Inject("claroline.manager.rights_manager")
     * })
     */
    public function __construct (
        ResourceTypeRepository $resourceTypeRepo,
        AbstractResourceRepository $resourceRepo,
        ResourceRightsRepository $resourceRightsRepo,
        IconManager $iconManager,
        ResourceWriter $writer,
        RightsManager $rightsManager
    )
    {
        $this->resourceTypeRepo = $resourceTypeRepo;
        $this->resourceRepo = $resourceRepo;
        $this->resourceRightsRepo = $resourceRightsRepo;
        $this->iconManager = $iconManager;
        $this->writer = $writer;
        $this->rightsManager = $rightsManager;
    }

    /**
     * define the array rights
     */
    public function create(
        AbstactResource $resource,
        $resourceType,
        User $creator,
        AbstractWorkspace $workspace,
        AbstractResource $parent = null,
        $icon = null,
        array $rights = null
    )
    {
        $resourceType = $this->resourceTypeRepo->findOneBy(array('name' => $resourceType));
        $name = $this->getUniqueName($resource, $parent);
        $previous = $this->resourceRepo->findOneBy(array('parent' => $parent, 'next' => null));
        $entityIcon = $this->generateIcon($icon);
        $resource = $this->writer->create(
            $resource,
            $resourceType,
            $creator,
            $workspace,
            $name,
            $entityIcon,
            $previous,
            $parent
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

        foreach ($children as $child) {
            $arChildName = explode('~', pathinfo($child->getName(), PATHINFO_FILENAME));
            if ($baseName === $arChildName[0]) {
                $nbName++;
            }
        }

        if (0 !== $nbName) {
            $newName = $baseName.'~'.$nbName.'.'.pathinfo($name, PATHINFO_EXTENSION);
        } else {
            $newName = $name;
        }

        return $newName;
    }

    public function getSiblings(AbstractResource $parent = null)
    {
        if ($parent !== null) {
            return $parent->getChildren();
        }

        return $this->resourceRepo->findBy(array('parent' => null));
    }

    public function generateIcon(AbstractResource $resource, $icon = null)
    {
        if ($icon === null) {
            return $this->iconManager->findResourceIcon($resource);
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
     * @todo
     * Define the $rights array.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\AbstractResource $resource
     * @param \Claroline\CoreBundle\Entity\Resource\AbstractResource $parent
     * @param array $rights
     */
    private function setRights(AbstractResource $resource, AbstractResource $parent, array $rights = array())
    {
        if (count($rights) === 0) {
            $this->rightsManager->cloneRights($parent, $resource);
        } else {
            $this->rightsManager->setRights($resource, $rights);
        }
    }
}