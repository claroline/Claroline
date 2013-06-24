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
            $resourceRights[] = $this->writer->create($this->getFalsePermissions(), array(), $resource, $role);
        }

        foreach ($resourceRights as $resourceRight) {
            $this->writer->edit($resourceRight, $permissions, $creations);
        }
    }

    public function editRights(AbstractResource $resource, Role $role, array $permissions, array $creations = array())
    {
        $rights = $this->rightsRepo->findOneBy(array('resource' => $resource, 'role' => $role));
        $this->writer->edit($rights, $permissions, $creations);

        return $rights;
    }

    public function cloneRights(AbstractResource $parent, AbstractResource $resource)
    {
       $resourceRights = $this->rightsRepo->findBy(array('resource' => $parent));
       $created = array();

       foreach ($resourceRights as $resourceRight) {
           $created[] = $this->writer->createFrom($resource, $resourceRight);
       }

       return $created;
    }

    /**
     * Sets the resource rights of a resource.
     * Expects an array of role of the following form:
     * array('ROLE_WS_MANAGER' => array('canOpen' => true, 'canEdit' => false', ...)
     * The 'canCopy' key must contain an array of resourceTypes name.
     * The role array must be structured this way:
     * 'ROLE_WS_MANAGER' => $entity
     */
    public function setRights(AbstractResource $resource, array $rights)
    {
        foreach ($rights as $data) {
            $resourceTypes = $this->checkResourceTypes($data['canCreate']);
            $this->writer->create($data, $resourceTypes, $resource, $data['role']);
        }
    }

    public function setAdminRights($resource)
    {
        $resourceTypes = $this->resourceTypeRepo->findAll();

        $this->writer->create(
            $this->getTruePermissions(),
            $resourceTypes,
            $resource,
            $this->roleRepo->findOneBy(array('name' => 'ROLE_ADMIN'))
        );
    }


    public function setAnonymousRights($resource)
    {
        $this->writer->create(
            $this->getFalsePermissions(),
            array(),
            $resource,
            $this->roleRepo->findOneBy(array('name' => 'ROLE_ANONYMOUS'))
        );
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
                $finalRights[] = $this->writer->create($this->getFalsePermissions(), array(), $resource, $role);
            }
        }

        return $finalRights;
    }

    public function getFalsePermissions()
    {
        return array(
            'canCopy' => false,
            'canOpen' => false,
            'canDelete' => false,
            'canEdit' => false,
            'canExport' => false
        );
    }

    public function getTruePermissions()
    {
        return array(
            'canCopy' => true,
            'canOpen' => true,
            'canDelete' => true,
            'canEdit' => true,
            'canExport' => true
        );
    }

    private function checkResourceTypes(array $resourceTypes)
    {
        $validTypes = array();
        $unknownTypes = array();
        foreach ($resourceTypes as $type) {
            //@todo write findByNames method.
            $rt = $this->resourceTypeRepo->findOneByName($type);
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

            throw new \Exception($content);
        }

        return $validTypes;
    }
}