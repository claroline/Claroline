<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager\Resource;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\BundleRecorder\Log\LoggableTrait;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceRights;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\Resource\UpdateResourceRightsEvent;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Repository\ResourceNodeRepository;
use Claroline\CoreBundle\Repository\ResourceRightsRepository;
use Claroline\CoreBundle\Repository\ResourceTypeRepository;
use Claroline\CoreBundle\Repository\RoleRepository;
use JMS\DiExtraBundle\Annotation as DI;
use Psr\Log\LogLevel;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Role\RoleInterface;

/**
 * @DI\Service("claroline.manager.rights_manager")
 *
 * @deprecated use OptimizedRightsManager instead
 */
class RightsManager
{
    use LoggableTrait;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var MaskManager */
    private $maskManager;
    /** @var ResourceRightsRepository */
    private $rightsRepo;
    /** @var ResourceNodeRepository */
    private $resourceRepo;
    /** @var RoleRepository */
    private $roleRepo;
    /** @var ResourceTypeRepository */
    private $resourceTypeRepo;
    /** @var ObjectManager */
    private $om;
    /** @var StrictDispatcher */
    private $dispatcher;
    /** @var RoleManager */
    private $roleManager;

    private $container;

    /**
     * RightsManager constructor.
     *
     * @DI\InjectParams({
     *     "tokenStorage" = @DI\Inject("security.token_storage"),
     *     "om"           = @DI\Inject("claroline.persistence.object_manager"),
     *     "dispatcher"   = @DI\Inject("claroline.event.event_dispatcher"),
     *     "roleManager"  = @DI\Inject("claroline.manager.role_manager"),
     *     "maskManager"  = @DI\Inject("claroline.manager.mask_manager"),
     *     "container"    = @DI\Inject("service_container")
     * })
     *
     * @param TokenStorageInterface $tokenStorage
     * @param ObjectManager         $om
     * @param StrictDispatcher      $dispatcher
     * @param RoleManager           $roleManager
     * @param MaskManager           $maskManager
     * @param ContainerInterface    $container
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        ObjectManager $om,
        StrictDispatcher $dispatcher,
        RoleManager $roleManager,
        MaskManager $maskManager,
        ContainerInterface $container
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->om = $om;
        $this->dispatcher = $dispatcher;
        $this->roleManager = $roleManager;
        $this->maskManager = $maskManager;
        $this->container = $container; // todo remove me (required because of a circular dependency with claroline.manager.resource_manager)

        $this->rightsRepo = $om->getRepository('ClarolineCoreBundle:Resource\ResourceRights');
        $this->resourceRepo = $om->getRepository('ClarolineCoreBundle:Resource\ResourceNode');
        $this->roleRepo = $om->getRepository('ClarolineCoreBundle:Role');
        $this->resourceTypeRepo = $om->getRepository('ClarolineCoreBundle:Resource\ResourceType');
    }

    /**
     * Create a new ResourceRight. If the ResourceRight already exists, it's edited instead.
     *
     * @param array|int    $permissions
     * @param Role         $role
     * @param ResourceNode $node
     * @param bool         $isRecursive
     * @param array        $creations
     *
     * @deprecated
     *
     * @todo remove me. This does the same thing than editPerms, this is just written a different way
     */
    public function create(
        $permissions,
        Role $role,
        ResourceNode $node,
        $isRecursive,
        array $creations = []
    ) {
        $rights = $this->rightsRepo->findBy(['role' => $role, 'resourceNode' => $node]);

        if (0 === count($rights)) {
            $isRecursive ?
                $this->recursiveCreation($permissions, $role, $node, $creations) :
                $this->nonRecursiveCreation($permissions, $role, $node, $creations);
        } else {
            $this->editPerms($permissions, $role, $node, $isRecursive, $creations);
        }
    }

    /**
     * @param array|int    $permissions - the permission mask
     * @param Role|string  $role
     * @param ResourceNode $node
     * @param bool         $isRecursive
     * @param array        $creations
     * @param bool         $mergePerms  - do we want to merge the permissions (only work for integers perm)
     *
     * @return ResourceRights[]
     */
    public function editPerms(
        $permissions,
        $role,
        ResourceNode $node,
        $isRecursive = false,
        array $creations = [],
        $mergePerms = false
    ) {
        $this->log('Editing permissions...');

        if (is_string($role)) {
            $role = $this->roleRepo->findOneBy(['name' => $role]);
        }

        $this->om->startFlushSuite();

        $arRights = $isRecursive ?
            $this->updateRightsTree($role, $node) :
            [$this->getOneByRoleAndResource($role, $node)];

        $this->log('Encoding masks for '.count($arRights).' elements...');

        foreach ($arRights as $toUpdate) {
            if ($isRecursive) {
                $resourceType = $toUpdate->getResourceNode()->getResourceType();
                if (!is_int($permissions)) {
                    $permissionsMask = $this->maskManager->encodeMask($permissions, $resourceType);
                    $permissions = $this->mergeTypePermissions($permissionsMask, $toUpdate->getMask(), $resourceType);
                } else {
                    $permissions = $this->mergeTypePermissions($permissions, $toUpdate->getMask(), $resourceType);
                }
                $this->log('Editing '.$toUpdate->getResourceNode()->getName().': old mask = '.$toUpdate->getMask().' | new mask = '.$permissions);
            }

            if (is_int($permissions)) {
                if ($mergePerms) {
                    $permissions = $permissions | $toUpdate->getMask();
                }
                $toUpdate->setMask($permissions);
            } else {
                $this->setPermissions($toUpdate, $permissions);
            }

            if (count($creations) > 0) {
                $toUpdate->setCreatableResourceTypes($creations);
            }

            $this->om->persist($toUpdate);

            //this is bad but for a huge datatree, logging everythings takes way too much time.
            //well, nowadays I think we can do this.
            if (!$isRecursive) {
                $this->logChangeSet($toUpdate);
                $this->dispatcher->dispatch('resource_change_permissions', UpdateResourceRightsEvent::class, [$node, $toUpdate]);
            }
        }

        $this->om->endFlushSuite();

        return $arRights;
    }

    /**
     * Copy the rights from the parent to its children.
     *
     * @param ResourceNode $original
     * @param ResourceNode $node
     *
     * @return ResourceNode
     */
    public function copy(ResourceNode $original, ResourceNode $node)
    {
        /** @var ResourceRights[] $originalRights */
        $originalRights = $this->rightsRepo->findBy(['resourceNode' => $original]);

        $this->om->startFlushSuite();
        foreach ($originalRights as $originalRight) {
            $new = $this->rightsRepo->findOneBy(['resourceNode' => $node, 'role' => $originalRight->getRole()]) ?? new ResourceRights();
            $new->setRole($originalRight->getRole());
            $new->setResourceNode($node);
            $new->setMask($originalRight->getMask());
            $new->setCreatableResourceTypes($originalRight->getCreatableResourceTypes()->toArray());
            $this->om->persist($new);
            $node->addRight($new);
        }
        $this->om->endFlushSuite();

        return $node;
    }

    /**
     * Create rights wich weren't created for every descendants and returns every rights of
     * every descendants (include rights wich weren't created).
     *
     * @param \Claroline\CoreBundle\Entity\Role                  $role
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $node
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceRights[]
     */
    public function updateRightsTree(Role $role, ResourceNode $node)
    {
        $this->log('Updating the right tree');

        /** @var ResourceRights[] $alreadyExisting */
        $alreadyExisting = $this->rightsRepo->findRecursiveByResourceAndRole($node, $role);
        $descendants = $this->resourceRepo->findDescendants($node, true);
        $finalRights = [];

        foreach ($descendants as $descendant) {
            $found = false;

            foreach ($alreadyExisting as $existingRight) {
                if ($existingRight->getResourceNode() === $descendant) {
                    $finalRights[] = $existingRight;
                    $found = true;
                }
            }

            if (!$found) {
                $rights = new ResourceRights();
                $rights->setRole($role);
                $rights->setResourceNode($descendant);
                $this->om->persist($rights);
                $finalRights[] = $rights;
            }
        }

        $this->om->flush();
        $this->log('Right tree updated');

        return $finalRights;
    }

    /**
     * Set the permission for a resource right.
     * The array of permissions should be defined that way:
     * array('open' => true, 'edit' => false, ...).
     *
     * @param ResourceRights $rights
     * @param array          $permissions
     *
     * @return ResourceRights
     */
    public function setPermissions(ResourceRights $rights, array $permissions)
    {
        $resourceType = $rights->getResourceNode()->getResourceType();
        $rights->setMask($this->maskManager->encodeMask($permissions, $resourceType));

        return $rights;
    }

    /**
     * @param Role         $role
     * @param ResourceNode $node
     *
     * @return ResourceRights $resourceRights
     */
    public function getOneByRoleAndResource(Role $role, ResourceNode $node)
    {
        /** @var ResourceRights $resourceRights */
        $resourceRights = $this->rightsRepo->findOneBy(['resourceNode' => $node, 'role' => $role]);
        if ($resourceRights) {
            return $resourceRights;
        }

        $resourceRights = new ResourceRights();
        $resourceRights->setResourceNode($node);
        $resourceRights->setRole($role);

        return $resourceRights;
    }

    /**
     * @param string[]     $roles
     * @param ResourceNode $node
     *
     * @return array
     */
    public function getCreatableTypes(array $roles, ResourceNode $node)
    {
        $creationRights = $this->rightsRepo->findCreationRights($roles, $node);

        return array_map(function (array $type) {
            return $type['name'];
        }, $creationRights);
    }

    /**
     * @param int|array    $permissions
     * @param Role         $role
     * @param ResourceNode $node
     * @param array        $creations
     */
    public function recursiveCreation(
        $permissions,
        Role $role,
        ResourceNode $node,
        array $creations = []
    ) {
        $this->om->startFlushSuite();
        //will create every rights with the role and the resource already set.
        $resourceRights = $this->updateRightsTree($role, $node);

        foreach ($resourceRights as $rights) {
            is_int($permissions) ? $rights->setMask($permissions) : $this->setPermissions($rights, $permissions);
            $rights->setCreatableResourceTypes($creations);
            $this->om->persist($rights);
        }

        $this->om->endFlushSuite();
    }

    /**
     * @param int|array    $permissions
     * @param Role         $role
     * @param ResourceNode $node
     * @param array        $creations
     *
     * @return ResourceRights
     */
    public function nonRecursiveCreation(
        $permissions,
        Role $role,
        ResourceNode $node,
        array $creations = []
    ) {
        $rights = new ResourceRights();
        $rights->setRole($role);
        $rights->setResourceNode($node);
        $rights->setCreatableResourceTypes($creations);
        is_int($permissions) ? $rights->setMask($permissions) : $this->setPermissions($rights, $permissions);
        $this->om->persist($rights);
        $this->om->flush();

        return $rights;
    }

    /**
     * @param ResourceRights $rights
     */
    public function logChangeSet(ResourceRights $rights)
    {
        $uow = $this->om->getUnitOfWork();
        $class = $this->om->getClassMetadata('Claroline\CoreBundle\Entity\Resource\ResourceRights');
        $uow->computeChangeSet($class, $rights);
        $changeSet = $uow->getEntityChangeSet($rights);

        if (count($changeSet) > 0) {
            $this->dispatcher->dispatch(
                'log',
                'Log\LogWorkspaceRoleChangeRight',
                [$rights->getRole(), $rights->getResourceNode(), $changeSet]
            );
        }
    }

    /**
     * Returns every ResourceRights of a resource on 1 level if the role linked is not 'ROLE_ADMIN'.
     *
     * @param ResourceNode $node
     *
     * @return ResourceRights[]
     *
     * @deprecated no longer used in new manager
     */
    public function getConfigurableRights(ResourceNode $node)
    {
        $existing = $this->rightsRepo->findConfigurableRights($node);
        $roles = $node->getWorkspace()->getRoles();
        $missings = [];

        foreach ($roles as $role) {
            $found = false;
            foreach ($existing as $right) {
                if ($right->getRole()->getId() === $role->getId()) {
                    $found = true;
                }
            }

            if (!$found) {
                $missings[] = $role;
            }
        }

        if (0 === count($missings)) {
            return $existing;
        }

        // might be slow on large data trees
        foreach ($missings as $missing) {
            $this->create([], $missing, $node, true, []);
        }

        return $this->rightsRepo->findConfigurableRights($node);
    }

    /**
     * @return ResourceType[]
     *
     * @deprecated
     *
     * @todo remove me I'm not related to Rights
     */
    public function getResourceTypes()
    {
        return $this->resourceTypeRepo->findAll();
    }

    /**
     * @param array        $roles
     * @param ResourceNode $node
     *
     * @return ResourceRights
     */
    public function getMaximumRights(array $roles, ResourceNode $node)
    {
        return $this->rightsRepo->findMaximumRights($roles, $node);
    }

    /**
     * @param string[]     $roles
     * @param ResourceNode $node
     *
     * @return array
     */
    public function getCreationRights(array $roles, ResourceNode $node)
    {
        return $this->rightsRepo->findCreationRights($roles, $node);
    }

    /**
     * Merges permissions related to a specific resource type (i.e. "post" in a
     * forum) with a directory mask. This allows directory permissions to be
     * applied recursively without losing particular permissions.
     *
     * @param int          $dirMask      A directory mask
     * @param int          $resourceMask A specific resource mask
     * @param ResourceType $resourceType
     *
     * @return int
     */
    private function mergeTypePermissions($dirMask, $resourceMask, ResourceType $resourceType)
    {
        $baseArray = [];
        $defaultActions = $this->maskManager->getDefaultActions();

        foreach ($defaultActions as $action) {
            $baseArray[$action] = true;
        }

        $basePerms = $this->maskManager->encodeMask($baseArray, $resourceType);

        //a little bit of magic goes here.
        $all = $resourceMask | $basePerms; //merge
        $typeMask = $all ^ $basePerms; //extract perm from type
        $this->log('resource mask is '.$resourceMask.'  | basePerms is '.$basePerms.' | base mask is '.$typeMask);

        return $dirMask | $typeMask; // merge perm from type and new perms
    }

    /**
     * Return the resource rights as a readable array. This array can be used for the resource creation.
     *
     * @param ResourceNode $node
     *
     * @return array
     *
     * @deprecated use getRights()
     */
    public function getCustomRoleRights(ResourceNode $node)
    {
        $perms = [];

        foreach ($node->getRights() as $right) {
            //if not ROLE_ANONYMOUS nor ROLE_USER because they're added automatically in ResourceManager::createRights
            if ('ROLE_ANONYMOUS' !== $right->getRole()->getName() && 'ROLE_USER' !== $right->getRole()->getName()) {
                $rolePerms = $this->maskManager->decodeMask($right->getMask(), $node->getResourceType());
                $perms[$right->getRole()->getName()] = $rolePerms;
                $perms[$right->getRole()->getName()]['role'] = $right->getRole();
                //no implementation for rights creations yet
                $perms[$right->getRole()->getName()]['create'] = [];
            }
        }

        return $perms;
    }

    public function getRights(ResourceNode $resourceNode, array $options = [])
    {
        return array_map(function (ResourceRights $rights) use ($resourceNode, $options) {
            $role = $rights->getRole();
            $permissions = $this->maskManager->decodeMask($rights->getMask(), $resourceNode->getResourceType());

            if ('directory' === $resourceNode->getResourceType()->getName()) {
                // ugly hack to only get create rights for directories (it's the only one that can handle it).
                $permissions = array_merge(
                    $permissions,
                    ['create' => $this->getCreatableTypes([$role->getName()], $resourceNode)]
                );
            }

            $data = [
                'translationKey' => $role->getTranslationKey(),
                'permissions' => $permissions,
                'id' => $rights->getId(),
            ];

            if (!in_array(Options::REFRESH_UUID, $options)) {
                $data['name'] = $role->getName();
            }

            if ($role->getWorkspace()) {
                $data['workspace']['code'] = $role->getWorkspace()->getCode();
            } else {
                $data['workspace'] = null;
            }

            return $data;
        }, $resourceNode->getRights()->toArray());
    }

    /**
     * Initialize the default permissions for a role list.
     * Directories are excluded.
     *
     * @param ResourceNode[] $nodes
     * @param Role[]         $roles
     */
    public function initializePermissions(array $nodes, array $roles)
    {
        $this->om->startFlushSuite();

        foreach ($nodes as $node) {
            foreach ($roles as $role) {
                $type = $node->getResourceType();
                $this->editPerms(
                    $type->getDefaultMask(),
                    $role,
                    $node,
                    false
                );
            }
        }

        $this->om->endFlushSuite();
    }

    public function getRightsFromIdentityMapOrScheduledForInsert($roleName, ResourceNode $resourceNode)
    {
        $res = null;
        $res = $this->getRightsFromIdentityMap($roleName, $resourceNode);

        if ($res) {
            return $res;
        }

        return $this->getRightsScheduledForInsert($roleName, $resourceNode);
    }

    public function getRightsScheduledForInsert($roleName, ResourceNode $resourceNode)
    {
        $scheduledForInsert = $this->om->getUnitOfWork()->getScheduledEntityInsertions();
        foreach ($scheduledForInsert as $entity) {
            if ($entity instanceof ResourceRights) {
                if ($entity->getRole()->getName() === $roleName &&
                    $entity->getResourceNode() === $resourceNode) {
                    return $entity;
                }
            }
        }

        return null;
    }

    public function getRightsFromIdentityMap($roleName, ResourceNode $resourceNode)
    {
        $map = $this->om->getUnitOfWork()->getIdentityMap();
        $result = null;

        if (!array_key_exists('Claroline\CoreBundle\Entity\Resource\ResourceRights', $map)) {
            return null;
        }

        //so it was in the identityMap hey !

        /** @var ResourceRights $right */
        foreach ($map['Claroline\CoreBundle\Entity\Resource\ResourceRights'] as $right) {
            if ($right->getRole()->getName() === $roleName &&
                $right->getResourceNode() === $resourceNode) {
                $result = $right;
            }
        }

        return $result;
    }

    public function getUserRolesResourceRights(
        ResourceNode $resource,
        array $keys,
        $executeQuery = true
    ) {
        return count($keys) > 0 ?
            $this->rightsRepo
                ->findUserRolesResourceRights($resource, $keys, $executeQuery) :
            [];
    }

    public function checkIntegrity()
    {
        $this->log('Checking roles integrity for resources... This may take a while.');

        /** @var Workspace[] $workspaces */
        $workspaces = $this->om->getRepository('Claroline\CoreBundle\Entity\Workspace\Workspace')->findAll();
        $this->om->startFlushSuite();
        $i = 0;

        foreach ($workspaces as $workspace) {
            $this->log('Checking '.$workspace->getCode().'...');
            /** @var ResourceNode $root */
            $root = $this->container->get('claroline.manager.resource_manager')->getWorkspaceRoot($workspace);
            $collaboratorRole = $this->roleManager->getCollaboratorRole($workspace);

            if ($root && $collaboratorRole) {
                $collaboratorFound = false;

                foreach ($root->getRights() as $right) {
                    if ($right->getRole()->getName() === $this->roleManager->getCollaboratorRole($workspace)->getName()) {
                        $collaboratorFound = true;
                    }
                }

                if (!$collaboratorFound) {
                    $this->log('Adding missing right on root for '.$workspace->getCode().'.', LogLevel::DEBUG);
                    $collaboratorRole = $this->roleManager->getCollaboratorRole($workspace);
                    $this->editPerms(5, $collaboratorRole, $root, true, [], true);
                    ++$i;

                    if (0 === $i % 3) {
                        $this->log('flushing...');
                        $this->om->forceFlush();
                        $this->om->clear();
                    }
                }
            }
        }

        $this->om->endFlushSuite();
    }

    /**
     * Checks if the current user is a manager of a resource.
     *
     * A user is a manager of a resource if :
     *   - It is the creator of the resource
     *   - It is the manager of the parent workspace
     *   - It is a platform admin
     *
     * @param ResourceNode $resourceNode
     *
     * @return bool
     */
    public function isManager(ResourceNode $resourceNode)
    {
        $token = $this->tokenStorage->getToken();

        // if user is anonymous return false
        if ('anon.' === $token) {
            return false;
        }

        $roleNames = array_map(function (RoleInterface $role) {
            return $role->getRole();
        }, $token->getRoles());

        $isWorkspaceUsurp = in_array('ROLE_USURPATE_WORKSPACE_ROLE', $roleNames);

        $workspace = $resourceNode->getWorkspace();

        //if we manage the workspace
        if ($workspace && $this->container->get('claroline.manager.workspace_manager')->isManager($workspace, $token)) {
            return true;
        }

        // If not workspace usurper
        if (!$isWorkspaceUsurp && $token->getUser() === $resourceNode->getCreator()) {
            return true;
        }

        if (in_array('ROLE_ADMIN', $roleNames)) {
            return true;
        }

        return false;
    }

    //maybe use that one in the voter later because it's going to be usefull
    public function getCurrentPermissionArray(ResourceNode $resourceNode)
    {
        $currentRoles = $this->tokenStorage->getToken()->getRoles();

        $roleNames = array_map(function (RoleInterface $roleName) {
            return $roleName->getRole();
        }, $currentRoles);

        $creatable = [];
        if ($this->isManager($resourceNode)) {
            /** @var ResourceType[] $resourceTypes */
            $resourceTypes = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findAll();

            foreach ($resourceTypes as $resourceType) {
                $creatable[] = $resourceType->getName();
            }

            $perms = array_fill_keys(array_values($this->maskManager->getPermissionMap($resourceNode->getResourceType())), true);
        } else {
            $creatable = $this->getCreatableTypes($roleNames, $resourceNode);

            $perms = $this->maskManager->decodeMask(
                $this->rightsRepo->findMaximumRights($roleNames, $resourceNode),
                $resourceNode->getResourceType()
            );
        }

        return array_merge($perms, ['create' => $creatable]);
    }
}
