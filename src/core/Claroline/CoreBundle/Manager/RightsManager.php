<?php

namespace Claroline\CoreBundle\Manager;

use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Database\Writer;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\ResourceRights;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Repository\ResourceRightsRepository;
use Claroline\CoreBundle\Repository\AbstractResourceRepository;
use Claroline\CoreBundle\Repository\RoleRepository;
use Claroline\CoreBundle\Repository\ResourceTypeRepository;
use Symfony\Component\Translation\Translator;

/**
 * @DI\Service("claroline.manager.rights_manager")
 */
class RightsManager extends AbstractManager
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
    /** @var Translator */
    private $translator;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "rightsRepo" =       @DI\Inject("resource_rights_repository"),
     *     "resourceRepo" =     @DI\Inject("resource_repository"),
     *     "roleRepo" =         @DI\Inject("role_repository"),
     *     "resourceTypeRepo" = @DI\Inject("resource_type_repository"),
     *     "writer" =           @DI\Inject("claroline.database.writer"),
     *     "translator" =       @DI\Inject("translator")
     * })
     */
    public function __construct(
        ResourceRightsRepository $rightsRepo,
        AbstractResourceRepository $resourceRepo,
        RoleRepository $roleRepo,
        ResourceTypeRepository $resourceTypeRepo,
        Writer $writer,
        Translator $translator
    )
    {
        $this->rightsRepo = $rightsRepo;
        $this->resourceRepo = $resourceRepo;
        $this->roleRepo = $roleRepo;
        $this->resourceTypeRepo = $resourceTypeRepo;
        $this->writer = $writer;
        $this->translator = $translator;
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
        $isRecursive ?
            $this->recursiveCreation($permissions, $role, $resource, $creations) :
            $this->nonRecursiveCreation($permissions, $role, $resource, $creations);
    }

    public function editPerms(
        array $permissions,
        Role $role,
        AbstractResource $resource,
        $isRecursive
    )
    {
        $this->writer->suspendFlush();
        $arRights = ($isRecursive) ?
            $this->updateRightsTree($role, $resource):
            array($this->getOneByRoleAndResource($role, $resource));

        foreach ($arRights as $toUpdate) {
            $this->setPermissions($toUpdate, $permissions);
            $this->writer->update($toUpdate);
        }

        $this->writer->forceFlush();

        return $arRights;
    }

    public function editCreationRights(
        array $resourceTypes,
        Role $role,
        AbstractResource $resource,
        $isRecursive
    )
    {
        $this->writer->suspendFlush();

        $arRights = ($isRecursive) ?
            $this->updateRightsTree($role, $resource):
            array($this->getOneByRoleAndResource($role, $resource));

        foreach ($arRights as $toUpdate) {
            $toUpdate->setCreatableResourceTypes($resourceTypes);
            $this->writer->update($toUpdate);
        }

        $this->writer->forceFlush();

        return $arRights;
    }

    public function copy(AbstractResource $original, AbstractResource $resource)
    {
       $originalRights = $this->rightsRepo->findBy(array('resource' => $original));
       $created = array();
       $this->writer->suspendFlush();

       foreach ($originalRights as $originalRight) {
           $created[] = $this->create(
               $originalRight->getPermissions(),
               $originalRight->getRole(),
               $resource,
               false,
               $originalRight->getCreatableResourceTypes()->toArray()
           );
       }

       $this->writer->forceFlush();

       return $created;
    }

    /**
     * Create rights wich weren't created for every descendants and returns every rights of
     * every descendants (include rights wich weren't created).
     *
     * @param \Claroline\CoreBundle\Entity\Role $role
     * @param \Claroline\CoreBundle\Entity\Resource\AbstractResource $resource
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceRights
     */
    public function updateRightsTree(Role $role, AbstractResource $resource)
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
                $rights = $this->getEntity('Resource\ResourceRights');
                $rights->setRole($role);
                $rights->setResource($descendant);
                $this->writer->create($rights);
                $finalRights[] = $rights;
            }
        }

        return $finalRights;
    }

    public function setPermissions(ResourceRights $rights, array $permissions)
    {
        $rights->setCanCopy($permissions['canCopy']);
        $rights->setCanOpen($permissions['canOpen']);
        $rights->setCanDelete($permissions['canDelete']);
        $rights->setCanEdit($permissions['canEdit']);
        $rights->setCanExport($permissions['canExport']);

        return $rights;
    }

    public function getOneByRoleAndResource(Role $role, AbstractResource $resource)
    {
        $resourceRights = $this->rightsRepo->findOneBy(array('resource' => $resource, 'role' => $role));

        if ($resourceRights === null) {
            $resourceRights = new ResourceRights();
            $resourceRights->setResource($resource);
            $resourceRights->setRole($role);
        }

        return $resourceRights;
    }

    /**
     * Returns every ResourceRights of a resource on 1 level if the role linked is not 'ROLE_ADMIN'
     *
     * @param \Claroline\CoreBundle\Entity\Resource\AbstractResource $resource
     *
     * @return array
     */
    public function getNonAdminRights(AbstractResource $resource)
    {
        return $this->rightsRepo->findNonAdminRights($resource);
    }

    public function getCreatableTypes(array $roles, Directory $directory)
    {
        $creatableTypes = array();
        $creationRights = $this->rightsRepo->findCreationRights($roles, $directory);

        if (count($creationRights) !== 0) {
            foreach ($creationRights as $type) {
                $creatableTypes[$type['name']] = $this->translator->trans($type['name'], array(), 'resource');
            }
        }

        return $creatableTypes;
    }

    private function recursiveCreation(
        array $permissions,
        Role $role,
        AbstractResource $resource,
        array $creations = array()
    ) {
        //will create every rights with the role and the resource already set.
        $resourceRights = $this->updateRightsTree($role, $resource);

        foreach ($resourceRights as $rights) {
            $this->setPermissions($rights, $permissions);
            $rights->setCreatableResourceTypes($creations);
            $this->writer->update($rights);
        }
    }

    private function nonRecursiveCreation(
        array $permissions,
        Role $role,
        AbstractResource $resource,
        array $creations = array()
    ) {
        $rights = $this->getEntity('Resource\ResourceRights');
        $rights->setRole($role);
        $rights->setResource($resource);
        $rights->setCreatableResourceTypes($creations);
        $this->setPermissions($rights, $permissions);
        $this->writer->create($rights);
    }

    public function getResourceTypes()
    {
       return $this->resourceTypeRepo->findAll();
    }
}