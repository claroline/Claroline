<?php

namespace Claroline\CoreBundle\Manager;

use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\ResourceRights;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Repository\ResourceRightsRepository;
use Claroline\CoreBundle\Repository\AbstractResourceRepository;
use Claroline\CoreBundle\Repository\RoleRepository;
use Claroline\CoreBundle\Repository\ResourceTypeRepository;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Symfony\Component\Translation\Translator;

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
    /** @var Translator */
    private $translator;
    /** @var ObjectManager */
    private $om;
    /** @var StrictDispatcher */
    private $dispatcher;
    /** @var RoleManager */
    private $roleManager;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "translator"  =    @DI\Inject("translator"),
     *     "om"          = @DI\Inject("claroline.persistence.object_manager"),
     *     "dispatcher"  = @DI\Inject("claroline.event.event_dispatcher"),
     *     "roleManager" = @DI\Inject("claroline.manager.role_manager")
     * })
     */
    public function __construct(
        Translator $translator,
        ObjectManager $om,
        StrictDispatcher $dispatcher,
        RoleManager $roleManager
    )
    {
        $this->rightsRepo = $om->getRepository('ClarolineCoreBundle:Resource\ResourceRights');
        $this->resourceRepo = $om->getRepository('ClarolineCoreBundle:Resource\AbstractResource');
        $this->roleRepo = $om->getRepository('ClarolineCoreBundle:Role');
        $this->resourceTypeRepo = $om->getRepository('ClarolineCoreBundle:Resource\ResourceType');
        $this->translator = $translator;
        $this->om = $om;
        $this->dispatcher = $dispatcher;
        $this->roleManager = $roleManager;
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
        //Bugfix: If the flushSuite is uncommented, doctrine returns an error
        //(ResourceRights duplicata)
        //$this->om->startFlushSuite();

        $arRights = ($isRecursive) ?
            $this->updateRightsTree($role, $resource):
            array($this->getOneByRoleAndResource($role, $resource));

        foreach ($arRights as $toUpdate) {
            $this->setPermissions($toUpdate, $permissions);
            $this->om->persist($toUpdate);
            $this->logChangeSet($toUpdate);
        }

        //$this->om->endFlushSuite();

        return $arRights;
    }

    public function editCreationRights(
        array $resourceTypes,
        Role $role,
        AbstractResource $resource,
        $isRecursive
    )
    {
        $this->om->startFlushSuite();

        $arRights = ($isRecursive) ?
            $this->updateRightsTree($role, $resource):
            array($this->getOneByRoleAndResource($role, $resource));

        foreach ($arRights as $toUpdate) {
            $toUpdate->setCreatableResourceTypes($resourceTypes);
            $this->om->persist($toUpdate);
            $this->logChangeSet($toUpdate);
        }

        $this->om->endFlushSuite();

        return $arRights;
    }

    public function copy(AbstractResource $original, AbstractResource $resource)
    {
       $originalRights = $this->rightsRepo->findBy(array('resource' => $original));
       $created = array();
       $this->om->startFlushSuite();

       foreach ($originalRights as $originalRight) {
           $created[] = $this->create(
               $originalRight->getPermissions(),
               $originalRight->getRole(),
               $resource,
               false,
               $originalRight->getCreatableResourceTypes()->toArray()
           );
       }

       $this->om->endFlushSuite();

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
                $rights = $this->om->factory('Claroline\CoreBundle\Entity\Resource\ResourceRights');
                $rights->setRole($role);
                $rights->setResource($descendant);
                $this->om->persist($rights);
                $finalRights[] = $rights;
            }
        }

        $this->om->flush();

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

    /**
     * Takes an array of Role.
     * Parse each key of the $perms array
     * and add the entry 'role' where it is needed.
     *
     * @param array $baseRoles
     * @param array $perms
     * @return array
     */
    public function addRolesToPermsArray(array $baseRoles, array $perms)
    {
        $initializedArray = array();

        foreach ($perms as $roleBaseName => $data) {
            foreach ($baseRoles as $baseRole) {
                if ($this->roleManager->getRoleBaseName($baseRole->getName()) === $roleBaseName) {
                    $data['role'] = $baseRole;
                    $initializedArray[$roleBaseName] = $data;
                }
            }
        }

        return $initializedArray;
    }

    public function getOneByRoleAndResource(Role $role, AbstractResource $resource)
    {
        $resourceRights = $this->rightsRepo->findOneBy(array('resource' => $resource, 'role' => $role));

        if ($resourceRights === null) {
            $resourceRights = $this->om->factory('Claroline\CoreBundle\Entity\Resource\ResourceRights');
            $resourceRights->setResource($resource);
            $resourceRights->setRole($role);
        }

        return $resourceRights;
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
        $this->om->startFlushSuite();
        //will create every rights with the role and the resource already set.
        $resourceRights = $this->updateRightsTree($role, $resource);

        foreach ($resourceRights as $rights) {
            $this->setPermissions($rights, $permissions);
            $rights->setCreatableResourceTypes($creations);
            $this->om->persist($rights);
        }
        $this->om->endFlushSuite();
    }

    private function nonRecursiveCreation(
        array $permissions,
        Role $role,
        AbstractResource $resource,
        array $creations = array()
    ) {
        $rights = $this->om->factory('Claroline\CoreBundle\Entity\Resource\ResourceRights');
        $rights->setRole($role);
        $rights->setResource($resource);
        $rights->setCreatableResourceTypes($creations);
        $this->setPermissions($rights, $permissions);
        $this->om->persist($rights);
        $this->om->flush();
    }

    public function logChangeSet(ResourceRights $rights)
    {
        $uow = $this->om->getUnitOfWork();
        $uow->computeChangeSets();
        $changeSet = $uow->getEntityChangeSet($rights);

        if (count($changeSet > 0)) {
            $this->dispatcher->dispatch(
                'log',
                'Log\LogWorkspaceRoleChangeRight',
                array($rights->getRole(), $rights->getResource(), $changeSet)
            );
        }
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

    public function getResourceTypes()
    {
       return $this->resourceTypeRepo->findAll();
    }

    public function getMaximumRights(array $roles, AbstractResource $resource)
    {
        return $this->rightsRepo->findMaximumRights($roles, $resource);
    }

    public function getCreationRights(array $roles, AbstractResource $resource)
    {
        return $this->rightsRepo->findCreationRights($roles, $resource);
    }
}