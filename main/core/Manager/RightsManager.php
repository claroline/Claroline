<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\BundleRecorder\Log\LoggableTrait;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceRights;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Repository\ResourceNodeRepository;
use Claroline\CoreBundle\Repository\ResourceRightsRepository;
use Claroline\CoreBundle\Repository\ResourceTypeRepository;
use Claroline\CoreBundle\Repository\RoleRepository;
use JMS\DiExtraBundle\Annotation as DI;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Service("claroline.manager.rights_manager")
 */
class RightsManager
{
    use LoggableTrait;

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
    /** @var Translator */
    private $translator;
    /** @var ObjectManager */
    private $om;
    /** @var StrictDispatcher */
    private $dispatcher;
    /** @var RoleManager */
    private $roleManager;

    private $container;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "translator"  = @DI\Inject("translator"),
     *     "om"          = @DI\Inject("claroline.persistence.object_manager"),
     *     "dispatcher"  = @DI\Inject("claroline.event.event_dispatcher"),
     *     "roleManager" = @DI\Inject("claroline.manager.role_manager"),
     *     "maskManager" = @DI\Inject("claroline.manager.mask_manager"),
     *     "container"   = @DI\Inject("service_container")
     * })
     */
    public function __construct(
        TranslatorInterface $translator,
        ObjectManager $om,
        StrictDispatcher $dispatcher,
        RoleManager $roleManager,
        MaskManager $maskManager,
        ContainerInterface $container
    ) {
        $this->rightsRepo = $om->getRepository('ClarolineCoreBundle:Resource\ResourceRights');
        $this->resourceRepo = $om->getRepository('ClarolineCoreBundle:Resource\ResourceNode');
        $this->roleRepo = $om->getRepository('ClarolineCoreBundle:Role');
        $this->resourceTypeRepo = $om->getRepository('ClarolineCoreBundle:Resource\ResourceType');
        $this->translator = $translator;
        $this->om = $om;
        $this->dispatcher = $dispatcher;
        $this->roleManager = $roleManager;
        $this->maskManager = $maskManager;
        $this->container = $container;
        $this->logger = null;
    }

    /**
     * Create a new ResourceRight. If the ResourceRight already exists, it's edited instead.
     *
     * @param array|int                                          $permissions
     * @param \Claroline\CoreBundle\Entity\Role                  $role
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $node
     * @param bool                                               $isRecursive
     * @param array                                              $creations
     */
    public function create(
        $permissions,
        Role $role,
        ResourceNode $node,
        $isRecursive,
        array $creations = []
    ) {
        $rights = $this->rightsRepo->findBy(['role' => $role, 'resourceNode' => $node]);

        if (count($rights) === 0) {
            $isRecursive ?
                $this->recursiveCreation($permissions, $role, $node, $creations) :
                $this->nonRecursiveCreation($permissions, $role, $node, $creations);
        } else {
            $this->editPerms($permissions, $role, $node, $isRecursive, $creations);
        }
    }

    /**
     * @param int                                                $permissions the permission mask
     * @param \Claroline\CoreBundle\Entity\Role                  $role
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $node
     * @param bool                                               $isRecursive
     * @param array                                              $creations
     * @param bool                                               $mergePerm   do we want to merge the permissions (only work for integers perm)
     *
     * @return array|\Claroline\CoreBundle\Entity\Resource\ResourceRights[]
     */
    public function editPerms(
        $permissions,
        Role $role,
        ResourceNode $node,
        $isRecursive = false,
        $creations = [],
        $mergePerms = false
    ) {
        $this->log('Editing permissions...');
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

            $this->om->persist($toUpdate);

            //this is bad but for a huge datatree, logging everythings takes way too much time.
            //well, nowadays I think we can do this.
            if (!$isRecursive) {
                $this->logChangeSet($toUpdate);
                $this->dispatcher->dispatch('resource_change_permissions', 'UpdateResourceRights', [$node, $toUpdate]);
            }
        }

        //exception for activities
        if ($node->getResourceType()->getName() === 'activity') {
            $resource = $this->container->get('claroline.manager.resource_manager')->getResourceFromNode($node);
            $this->container->get('claroline.manager.activity_manager')->initializePermissions($resource);
        }

        if (count($creations) > 0) {
            $this->editCreationRights($creations, $role, $node, $isRecursive);
        }

        $this->om->endFlushSuite();

        return $arRights;
    }

    /**
     * @param array                                              $resourceTypes
     * @param \Claroline\CoreBundle\Entity\Role                  $role
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $node
     * @param bool                                               $isRecursive
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceRight[] $arRights
     */
    public function editCreationRights(
        array $resourceTypes,
        Role $role,
        ResourceNode $node,
        $isRecursive
    ) {
        //Bugfix: If the flushSuite is uncommented, doctrine returns an error

        $arRights = ($isRecursive) ?
            $this->updateRightsTree($role, $node) :
            [$this->getOneByRoleAndResource($role, $node)];

        foreach ($arRights as $toUpdate) {
            $toUpdate->setCreatableResourceTypes($resourceTypes);
            $this->om->persist($toUpdate);
            $this->logChangeSet($toUpdate);
        }

        return $arRights;
    }

    /**
     * Copy the rights from the parent to its children.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $original
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $node
     */
    public function copy(ResourceNode $original, ResourceNode $node)
    {
        $originalRights = $this->rightsRepo->findBy(['resourceNode' => $original]);
        $this->om->startFlushSuite();

        foreach ($originalRights as $originalRight) {
            $new = new ResourceRights();
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
        $alreadyExistings = $this->rightsRepo->findRecursiveByResourceAndRole($node, $role);
        $descendants = $this->resourceRepo->findDescendants($node, true);
        $finalRights = [];

        foreach ($descendants as $descendant) {
            $found = false;

            foreach ($alreadyExistings as $existingRight) {
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
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceRights $rights
     * @param array                                                $permissions
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceRights
     */
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
     * It's used when a workspace is imported.
     *
     * @param array $baseRoles
     * @param array $perms
     *
     * @return array
     */
    public function addRolesToPermsArray(array $baseRoles, array $perms)
    {
        $initializedArray = [];

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

    /**
     * @param \Claroline\CoreBundle\Entity\Role                  $role
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $node
     * @param bool                                               $fetchUOW
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceRights $resourceRights
     */
    public function getOneByRoleAndResource(Role $role, ResourceNode $node)
    {
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
     * @todo we don't need to return the translation keys. This needs to be change for the new resource manager
     *
     * @param string[]                                           $roles
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $node
     *
     * @return array
     */
    public function getCreatableTypes(array $roles, ResourceNode $node)
    {
        $creatableTypes = [];
        $creationRights = $this->rightsRepo->findCreationRights($roles, $node);

        if (count($creationRights) !== 0) {
            foreach ($creationRights as $type) {
                $creatableTypes[$type['name']] = $this->translator->trans($type['name'], [], 'resource');
            }
        }

        return $creatableTypes;
    }

    /**
     * @param int|array                                          $permissions
     * @param \Claroline\CoreBundle\Entity\Role                  $role
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $node
     * @param array                                              $creations
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
     * @param int|array                                          $permissions
     * @param \Claroline\CoreBundle\Entity\Role                  $role
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $node
     * @param array                                              $creations
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
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceRights $rights
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
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $node
     *
     * @return \Claroline\CoreBundle\Resource\ResourceRights[]
     */
    public function getConfigurableRights(ResourceNode $node)
    {
        return $this->rightsRepo->findConfigurableRights($node);
    }

    /**
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceType[]
     */
    public function getResourceTypes()
    {
        return $this->resourceTypeRepo->findAll();
    }

    /**
     * @param array                                              $roles
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $node
     *
     * @return type
     */
    public function getMaximumRights(array $roles, ResourceNode $node)
    {
        return $this->rightsRepo->findMaximumRights($roles, $node);
    }

    /**
     * @param string[]                                           $roles
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $node
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
     * @param int $dirMask      A directory mask
     * @param int $resourceMask A specific resource mask
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
     */
    public function getCustomRoleRights(ResourceNode $node)
    {
        $perms = [];

        foreach ($node->getRights() as $right) {
            //if not ROLE_ANONYMOUS nor ROLE_USER because they're added automatically in ResourceManager::createRights
            if ($right->getRole()->getName() !== 'ROLE_ANONYMOUS' && $right->getRole()->getName() !== 'ROLE_USER') {
                $rolePerms = $this->maskManager->decodeMask($right->getMask(), $node->getResourceType());
                $perms[$right->getRole()->getName()] = $rolePerms;
                $perms[$right->getRole()->getName()]['role'] = $right->getRole();
                //no implementation for rights creations yet
                $perms[$right->getRole()->getName()]['create'] = [];
            }
        }

        return $perms;
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
        $res = null;

        foreach ($scheduledForInsert as $entity) {
            if (get_class($entity) === 'Claroline\CoreBundle\Entity\Resource\ResourceRights') {
                if ($entity->getRole()->getName() === $roleName &&
                    $entity->getResourceNode() === $resourceNode) {
                    return $res = $entity;
                }
            }
        }

        return $res;
    }

    public function getRightsFromIdentityMap($roleName, ResourceNode $resourceNode)
    {
        $map = $this->om->getUnitOfWork()->getIdentityMap();
        $result = null;

        if (!array_key_exists('Claroline\CoreBundle\Entity\Resource\ResourceRights', $map)) {
            return;
        }

        //so it was in the identityMap hey !
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
        $workspaces = $this->om->getRepository('Claroline\CoreBundle\Entity\Workspace\Workspace')->findAll();
        $this->om->startFlushSuite();
        $i = 0;

        foreach ($workspaces as $workspace) {
            $this->log('Checking '.$workspace->getCode().'...');
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

                    if ($i % 3 === 0) {
                        $this->log('flushing...');
                        $this->om->forceFlush();
                        $this->om->clear();
                    }
                }
            }
        }

        $this->om->endFlushSuite();
    }

    public function isManager(ResourceNode $resourceNode)
    {
        $token = $this->container->get('security.token_storage')->getToken();

        // if user is anonymous return false
        if ($token === 'anon.') {
            return false;
        }

        $roleNames = array_map(
            function ($role) {
                return $role->getRole();
            },
            $token->getRoles()
        );

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
        $currentRoles = $this->container->get('security.token_storage')->getToken()->getRoles();

        $roleNames = array_map(function ($roleName) {
            return $roleName->getRole();
        }, $currentRoles);

        //si manager, retourne tout

        if ($this->isManager($resourceNode, $this->container->get('security.token_storage')->getToken())) {
            $resourceTypes = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findAll();

            foreach ($resourceTypes as $resourceType) {
                $creatables[$resourceType->getName()] = $this->translator->trans($resourceType->getName(), [], 'resource');
            }

            $perms = array_fill_keys(array_values($this->maskManager->getPermissionMap($resourceNode->getResourceType())), true);
        } else {
            $creatables = $this->getCreatableTypes($roleNames, $resourceNode);

            $perms = $this->maskManager->decodeMask(
            $this->rightsRepo->findMaximumRights($roleNames, $resourceNode),
            $resourceNode->getResourceType()
          );
        }

        return array_merge(['create' => $creatables], $perms);
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
