<?php

namespace Claroline\CoreBundle\Manager;

use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceRights;
use Claroline\CoreBundle\Repository\ResourceNodeRepository;
use Claroline\CoreBundle\Repository\RoleRepository;
use Claroline\CoreBundle\Repository\ResourceTypeRepository;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\MaskManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Symfony\Component\Translation\Translator;

/**
 * @DI\Service("claroline.manager.rights_manager")
 */
class RightsManager
{
    /** @var MaskManager */
    private $maskManager;

    private $rightsRepo;
    /** @var ResourceNodeRepository */
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
     *     "translator"  = @DI\Inject("translator"),
     *     "om"          = @DI\Inject("claroline.persistence.object_manager"),
     *     "dispatcher"  = @DI\Inject("claroline.event.event_dispatcher"),
     *     "roleManager" = @DI\Inject("claroline.manager.role_manager"),
     *     "maskManager" = @DI\Inject("claroline.manager.mask_manager")
     * })
     */
    public function __construct(
        Translator $translator,
        ObjectManager $om,
        StrictDispatcher $dispatcher,
        RoleManager $roleManager,
        MaskManager $maskManager
    )
    {
        $this->rightsRepo = $om->getRepository('ClarolineCoreBundle:Resource\ResourceRights');
        $this->resourceRepo = $om->getRepository('ClarolineCoreBundle:Resource\ResourceNode');
        $this->roleRepo = $om->getRepository('ClarolineCoreBundle:Role');
        $this->resourceTypeRepo = $om->getRepository('ClarolineCoreBundle:Resource\ResourceType');
        $this->translator = $translator;
        $this->om = $om;
        $this->dispatcher = $dispatcher;
        $this->roleManager = $roleManager;
        $this->maskManager = $maskManager;
    }

    /**
     * Create a new ResourceRight
     *
     * @param array                                              $permissions
     * @param boolean                                            $isRecursive
     * @param \Claroline\CoreBundle\Entity\Role                  $role
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $node
     */
    public function create(
        array $permissions,
        Role $role,
        ResourceNode $resource,
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
        ResourceNode $node,
        $isRecursive
    )
    {
        //Bugfix: If the flushSuite is uncommented, doctrine returns an error
        //(ResourceRights duplicata)
        //$this->om->startFlushSuite();

        $arRights = ($isRecursive) ?
            $this->updateRightsTree($role, $node):
            array($this->getOneByRoleAndResource($role, $node));

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
        ResourceNode $node,
        $isRecursive
    )
    {
        //Bugfix: If the flushSuite is uncommented, doctrine returns an error
        //(ResourceRights duplicata)
        //$this->om->startFlushSuite();

        $arRights = ($isRecursive) ?
            $this->updateRightsTree($role, $node):
            array($this->getOneByRoleAndResource($role, $node));

        foreach ($arRights as $toUpdate) {
            $toUpdate->setCreatableResourceTypes($resourceTypes);
            $this->om->persist($toUpdate);
            $this->logChangeSet($toUpdate);
        }

        //$this->om->endFlushSuite();
        return $arRights;
    }

    public function copy(ResourceNode $original, ResourceNode $node)
    {
        $originalRights = $this->rightsRepo->findBy(array('resourceNode' => $original));
        $created = array();
        $this->om->startFlushSuite();

        foreach ($originalRights as $originalRight) {
            $new = $this->om->factory('Claroline\CoreBundle\Entity\Resource\ResourceRights');
            $new->setRole($originalRight->getRole());
            $new->setResourceNode($node);
            $new->setMask($originalRight->getMask());
            $new->setCreatableResourceTypes($originalRight->getCreatableResourceTypes()->toArray());
            $this->om->persist($new);
        }

        $this->om->endFlushSuite();

        return $created;
    }

    /**
     * Create rights wich weren't created for every descendants and returns every rights of
     * every descendants (include rights wich weren't created).
     *
     * @param \Claroline\CoreBundle\Entity\Role                  $role
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $node
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceRights
     */
    public function updateRightsTree(Role $role, ResourceNode $node)
    {
        $alreadyExistings = $this->rightsRepo->findRecursiveByResourceAndRole($node, $role);
        $descendants = $this->resourceRepo->findDescendants($node, true);
        $finalRights = array();

        foreach ($descendants as $descendant) {
            $found = false;

            foreach ($alreadyExistings as $existingRight) {
                if ($existingRight->getResourceNode() === $descendant) {
                    $finalRights[] = $existingRight;
                    $found = true;
                }
            }

            if (!$found) {
                $rights = $this->om->factory('Claroline\CoreBundle\Entity\Resource\ResourceRights');
                $rights->setRole($role);
                $rights->setResourceNode($descendant);
                $this->om->persist($rights);
                $finalRights[] = $rights;
            }
        }

        $this->om->flush();

        return $finalRights;
    }

    public function setPermissions(ResourceRights $rights, array $permissions)
    {
        $resourceType = $rights->getResourceNode()->getResourceType();
        $rights->setMask($this->maskManager->encodeMask($permissions, $resourceType));

        return $rights;
    }

    /**
     * Takes an array of Role.
     * Parse each key of the $perms array
     * and add the entry 'role' where it is needed.
     *
     * @param array $baseRoles
     * @param array $perms
     *
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

    public function getOneByRoleAndResource(Role $role, ResourceNode $node)
    {
        $resourceRights = $this->rightsRepo->findOneBy(array('resourceNode' => $node, 'role' => $role));

        if ($resourceRights === null) {
            $resourceRights = $this->om->factory('Claroline\CoreBundle\Entity\Resource\ResourceRights');
            $resourceRights->setResourceNode($node);
            $resourceRights->setRole($role);
        }

        return $resourceRights;
    }

    public function getCreatableTypes(array $roles, ResourceNode $node)
    {
        $creatableTypes = array();
        $creationRights = $this->rightsRepo->findCreationRights($roles, $node);

        if (count($creationRights) !== 0) {
            foreach ($creationRights as $type) {
                $creatableTypes[$type['name']] = $this->translator->trans($type['name'], array(), 'resource');
            }
        }

        return $creatableTypes;
    }

    public function recursiveCreation(
        array $permissions,
        Role $role,
        ResourceNode $node,
        array $creations = array()
    )
    {
        $this->om->startFlushSuite();
        //will create every rights with the role and the resource already set.
        $resourceRights = $this->updateRightsTree($role, $node);

        foreach ($resourceRights as $rights) {
            $this->setPermissions($rights, $permissions);
            $rights->setCreatableResourceTypes($creations);
            $this->om->persist($rights);
        }

        $this->om->endFlushSuite();
    }

    public function nonRecursiveCreation(
        array $permissions,
        Role $role,
        ResourceNode $node,
        array $creations = array()
    )
    {
        $rights = $this->om->factory('Claroline\CoreBundle\Entity\Resource\ResourceRights');
        $rights->setRole($role);
        $rights->setResourceNode($node);
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
                array($rights->getRole(), $rights->getResourceNode(), $changeSet)
            );
        }
    }

    /**
     * Returns every ResourceRights of a resource on 1 level if the role linked is not 'ROLE_ADMIN'
     *
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $node
     *
     * @return array
     */
    public function getNonAdminRights(ResourceNode $node)
    {
        return $this->rightsRepo->findNonAdminRights($node);
    }

    public function getResourceTypes()
    {
        return $this->resourceTypeRepo->findAll();
    }

    public function getMaximumRights(array $roles, ResourceNode $node)
    {
        return $this->rightsRepo->findMaximumRights($roles, $node);
    }

    public function getCreationRights(array $roles, ResourceNode $node)
    {
        return $this->rightsRepo->findCreationRights($roles, $node);
    }
}
