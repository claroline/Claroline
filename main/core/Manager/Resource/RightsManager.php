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
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\BundleRecorder\Log\LoggableTrait;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceRights;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Repository\ResourceNodeRepository;
use Claroline\CoreBundle\Repository\ResourceRightsRepository;
use Claroline\CoreBundle\Repository\ResourceTypeRepository;
use Claroline\CoreBundle\Repository\RoleRepository;
use Psr\Log\LogLevel;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Role\RoleInterface;

/**
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
    /** @var RoleManager */
    private $roleManager;

    private $container;

    /**
     * RightsManager constructor.
     *
     * @param TokenStorageInterface $tokenStorage
     * @param ObjectManager         $om
     * @param RoleManager           $roleManager
     * @param MaskManager           $maskManager
     * @param ContainerInterface    $container
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        ObjectManager $om,
        RoleManager $roleManager,
        MaskManager $maskManager,
        ContainerInterface $container
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->om = $om;
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
    public function create($permissions, Role $role, ResourceNode $node, $isRecursive, array $creations = [])
    {
        $this->editPerms($permissions, $role, $node, $isRecursive, $creations);
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
    public function editPerms($permissions, $role, ResourceNode $node, $isRecursive = false, array $creations = [], $mergePerms = false)
    {
        $newRightsManager = $this->container->get('Claroline\CoreBundle\Manager\Resource\OptimizedRightsManager');
        $resourceType = $node->getResourceType();

        $mask = !is_int($permissions) ?
          $this->maskManager->encodeMask($permissions, $resourceType) :
          $permissions;

        $newRightsManager->update($node, $role, $mask, $creations, $isRecursive);
    }

    /**
     * Copy the rights from the parent to its children.
     * Should be removed sooner than later (see resourceNode copy).
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
