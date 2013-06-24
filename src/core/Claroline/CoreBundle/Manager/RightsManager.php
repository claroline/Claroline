<?php

namespace Claroline\CoreBundle\Manager;

use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Writer\RightsWriter;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Repository\ResourceRightsRepository;
use Claroline\CoreBundle\Repository\AbstractResourceRepository;
use Claroline\CoreBundle\Repository\RoleRepository;
use Claroline\CoreBundle\Repository\ResourceTypeRepository;

/**
 * @DI\Service("claroline.manager.rights_manager")
 */
class RightsManager
{
    /** @var RightsWriter */
    private $writer;
    /** @var ResourceRightsRepository */
    private $rightsRepo;
    /** @var AbstractResourceRepository */
    private $resourceRepo;
    /** @var RoleRepository */
    private $roleRepo;
    /** @var ResourceTypeRepository */
    private $resourceTypeRepo;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "writer" = @DI\Inject("claroline.writer.rights_writer"),
     *     "rightsRepo" = @DI\Inject("resource_rights_repository"),
     *     "resourceRepo" = @DI\Inject("resource_repository"),
     *     "roleRepo" = @DI\Inject("role_repository"),
     *     "resourceTypeRepo" = @DI\Inject("resource_type_repository")
     * })
     */
    public function __construct(
        RightsWriter $writer,
        ResourceRightsRepository $rightsRepo,
        AbstractResourceRepository $resourceRepo,
        RoleRepository $roleRepo,
        ResourceTypeRepository $resourceTypeRepo
    )
    {
        $this->writer = $writer;
        $this->rightsRepo = $rightsRepo;
        $this->resourceRepo = $resourceRepo;
        $this->roleRepo = $roleRepo;
        $this->resourceTypeRepo = $resourceTypeRepo;
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
            $resourceRights[] = $this->writer->create(
                array(
                    'canDelete' => false,
                    'canOpen' => false,
                    'canEdit' => false,
                    'canCopy' => false,
                    'canExport' => false,
                ),
                array(),
                $resource,
                $role
            );
        }

        foreach ($resourceRights as $resourceRight) {
            $this->writer->edit($resourceRight, $permissions, $creations);
        }
    }

    public function edit(AbstractResource $resource, Role $role, array $permissions, array $creations = array())
    {
        $rights = $this->rightsRepo->findOneBy(array('resource' => $resource, 'role' => $role));
        $this->writer->edit($rights, $permissions, $creations);

        return $rights;
    }

    public function copy(AbstractResource $original, AbstractResource $resource)
    {
       $resourceRights = $this->rightsRepo->findBy(array('resource' => $original));
       $created = array();

       foreach ($resourceRights as $resourceRight) {
           $created[] = $this->writer->createFrom($resource, $resourceRight);
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
                $finalRights[] = $this->writer->create(
                    array(
                        'canDelete' => false,
                        'canOpen' => false,
                        'canEdit' => false,
                        'canCopy' => false,
                        'canExport' => false,
                    ),
                    array(),
                    $resource,
                    $role
                );
            }
        }

        return $finalRights;
    }
}