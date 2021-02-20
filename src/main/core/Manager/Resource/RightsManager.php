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
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceRights;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Repository\Resource\ResourceNodeRepository;
use Claroline\CoreBundle\Repository\Resource\ResourceRightsRepository;
use Claroline\CoreBundle\Repository\Resource\ResourceTypeRepository;
use Claroline\CoreBundle\Repository\User\RoleRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @deprecated use OptimizedRightsManager instead
 */
class RightsManager
{
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
     * @deprecated
     *
     * @todo remove me. This does the same thing than editPerms, this is just written a different way
     */
    public function create($permissions, Role $role, ResourceNode $node, $isRecursive, array $creations = [], $log = true)
    {
        $this->editPerms($permissions, $role, $node, $isRecursive, $creations, $log);
    }

    /**
     * @param array|int   $permissions - the permission mask
     * @param Role|string $role
     * @param bool        $isRecursive
     * @param bool        $log
     */
    public function editPerms($permissions, $role, ResourceNode $node, $isRecursive = false, array $creations = [], $log = true)
    {
        $newRightsManager = $this->container->get('claroline.manager.optimized_rights_manager');
        $resourceType = $node->getResourceType();

        $mask = !is_int($permissions) ?
          $this->maskManager->encodeMask($permissions, $resourceType) :
          $permissions;

        $newRightsManager->update($node, $role, $mask, $creations, $isRecursive, $log);
    }

    /**
     * Copy the rights from the parent to its children.
     * Should be removed sooner than later (see resourceNode copy).
     */
    public function copy(ResourceNode $original, ResourceNode $node): ResourceNode
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

    public function getCreatableTypes(array $roles, ResourceNode $node): array
    {
        $creationRights = $this->rightsRepo->findCreationRights($roles, $node);

        return array_map(function (array $type) {
            return $type['name'];
        }, $creationRights);
    }

    public function getMaximumRights(array $roles, ResourceNode $node)
    {
        return $this->rightsRepo->findMaximumRights($roles, $node);
    }

    // TODO : this should be done by a serializer
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
                'id' => $rights->getId(),
                'translationKey' => $role->getTranslationKey(),
                'permissions' => $permissions,
                'workspace' => null,
            ];

            if (!in_array(Options::REFRESH_UUID, $options)) {
                $data['name'] = $role->getName();
            }

            if ($role->getWorkspace()) {
                $data['workspace']['id'] = $role->getWorkspace()->getUuid();
                $data['workspace']['code'] = $role->getWorkspace()->getCode();
                $data['workspace']['name'] = $role->getWorkspace()->getName();
            }

            return $data;
        }, $resourceNode->getRights()->toArray());
    }

    /**
     * Checks if the current user is a manager of a resource.
     *
     * A user is a manager of a resource if :
     *   - It is the creator of the resource
     *   - It is the manager of the parent workspace
     *   - It is a platform admin
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

        $roleNames = $token->getRoleNames();

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

    //maybe use that one in the voter later because it's going to be useful
    public function getCurrentPermissionArray(ResourceNode $resourceNode)
    {
        $roleNames = $this->tokenStorage->getToken()->getRoleNames();

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
