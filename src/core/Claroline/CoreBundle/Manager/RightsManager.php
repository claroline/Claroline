<?php

namespace Claroline\CoreBundle\Manager;

use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Database\Writer;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\ResourceRights;
use Claroline\CoreBundle\Repository\ResourceRightsRepository;
use Claroline\CoreBundle\Repository\AbstractResourceRepository;
use Claroline\CoreBundle\Repository\RoleRepository;
use Claroline\CoreBundle\Repository\ResourceTypeRepository;

/**
 * @DI\Service("claroline.manager.rights_manager")
 */
class RightsManager
{
    /** @var ResourceRightsRepository */
    private $rightsRepo;
    /** @var AbstractResourceRepository */
    private $resourceRepo;
    /** @var RoleRepository */
    private $roleRepo;
    /** @var ResourceTypeRepository */
    private $resourceTypeRepo;
    /** @var Writer */
    private $writer;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "rightsRepo" = @DI\Inject("resource_rights_repository"),
     *     "resourceRepo" = @DI\Inject("resource_repository"),
     *     "roleRepo" = @DI\Inject("role_repository"),
     *     "resourceTypeRepo" = @DI\Inject("resource_type_repository"),
     *     "writer" = @DI\Inject("claroline.database.writer")
     * })
     */
    public function __construct(
        ResourceRightsRepository $rightsRepo,
        AbstractResourceRepository $resourceRepo,
        RoleRepository $roleRepo,
        ResourceTypeRepository $resourceTypeRepo,
        Writer $writer
    )
    {
        $this->rightsRepo = $rightsRepo;
        $this->resourceRepo = $resourceRepo;
        $this->roleRepo = $roleRepo;
        $this->resourceTypeRepo = $resourceTypeRepo;
        $this->writer = $writer;
    }

    /**
     * Create a new ResourceRight
     *
     * @param array $permissions
     * @param boolean $isRecursive
     * @param \Claroline\CoreBundle\Entity\Role $role
     * @param \Claroline\CoreBundle\Entity\Resource\AbstractResource $resource
     */
    public function create(
        array $permissions,
        Role $role,
        AbstractResource $resource,
        $isRecursive,
        array $creations = array()
    )
    {
        $resourceRights = array();

        if ($isRecursive) {
            $resourceRights = $this->addMissingForDescendants($role, $resource);
        } else {
            $rights = new ResourceRights();
            $rights->setRole($role);
            $rights->setResource($resource);
            $this->writer->create($rights);
        }

        foreach ($resourceRights as $rights) {
            $this->setPermissions($rights, $permissions);
            $rights->setCreatableResourceTypes($creations);
            $this->writer->update($rights);
        }
    }

    public function edit(AbstractResource $resource, Role $role, array $permissions, array $creations = array())
    {
        $rights = $this->rightsRepo->findOneBy(array('resource' => $resource, 'role' => $role));
        $this->setPermissions($rights, $permissions);
        $rights->setCreatableResourceTypes($creations);
        $this->writer->update($rights);

        return $rights;
    }

    public function copy(AbstractResource $original, AbstractResource $resource)
    {
       $originalRights = $this->rightsRepo->findBy(array('resource' => $original));
       $created = array();

       foreach ($originalRights as $originalRight) {
            $rights = new ResourceRights();
            $rights->setResource($resource);
            $rights->setRole($originalRight->getRole());
            $rights->setRightsFrom($originalRight);

            if ($resource->getResourceType()->getName() === 'directory') {
                $rights->setCreatableResourceTypes($originalRight->getCreatableResourceTypes()->toArray());
            }
           $created[] = $this->writer->update($rights);
       }

       return $created;
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Role $role
     * @param \Claroline\CoreBundle\Entity\Resource\AbstractResource $resource
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceRights
     */
    public function addMissingForDescendants(Role $role, AbstractResource $resource)
    {
        $alreadyExistings = $this->rightsRepo->findRecursiveByResourceAndRole($resource, $role);
        $descendants = $this->resourceRepo->findDescendants($resource, true);
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
                $rights = new ResourceRights();
                $rights->setRole($role);
                $rights->setResource($resource);
                $this->writer->create($rights);
                $finalRights[] = $rights;
            }
        }

        return $finalRights;
    }

    private function setPermissions(ResourceRights $rights, array $permissions)
    {
        $rights->setCanCopy($permissions['canCopy']);
        $rights->setCanOpen($permissions['canOpen']);
        $rights->setCanDelete($permissions['canDelete']);
        $rights->setCanEdit($permissions['canEdit']);
        $rights->setCanExport($permissions['canExport']);

        return $rights;
    }
}