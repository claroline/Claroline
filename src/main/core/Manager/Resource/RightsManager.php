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

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\MaskDecoder;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceRights;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Claroline\CoreBundle\Repository\Resource\ResourceRightsRepository;
use Claroline\CoreBundle\Security\PlatformRoles;
use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class RightsManager
{
    private ResourceRightsRepository $rightsRepo;

    public function __construct(
        private readonly Connection $conn,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ObjectManager $om,
        private readonly SerializerProvider $serializer,
        private readonly MaskManager $maskManager,
        private readonly WorkspaceManager $workspaceManager
    ) {
        $this->rightsRepo = $om->getRepository(ResourceRights::class);
    }

    /**
     * @param array|int $permissions - either an array of perms or an encoded mask
     */
    public function create(array|int $permissions, Role $role, ResourceNode $node, ?bool $isRecursive = false, ?array $creations = []): void
    {
        $this->update($permissions, $role, $node, $isRecursive, $creations);
    }

    /**
     * @param array|int $permissions - either an array of perms or an encoded mask
     */
    public function update(array|int $permissions, Role $role, ResourceNode $node, ?bool $isRecursive = false, ?array $creations = []): void
    {
        if (!is_int($permissions)) {
            $mask = $this->maskManager->encodeMask($permissions, $node->getResourceType());
        } else {
            $mask = $permissions;
        }

        if (!$node->getId() || !$role->getId()) {
            if (!$node->getId()) {
                $this->om->persist($node);
            }

            if (!$role->getId()) {
                $this->om->persist($role);
            }

            // we really need it because we use ids to do pure SQL later
            $this->om->forceFlush();
        }

        if ($isRecursive) {
            $this->recursiveUpdate($node, $role, $mask, $creations);
        } else {
            $this->singleUpdate($node, $role, $mask, $creations);
        }
    }

    /**
     * Copy the rights from the parent to its children.
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
            $new->setCreatableResourceTypes($originalRight->getCreatableResourceTypes());
            $node->addRight($new);

            $this->om->persist($new);
        }
        $this->om->endFlushSuite();

        return $node;
    }

    public function getMaximumRights(array $roles, ResourceNode $node)
    {
        return $this->rightsRepo->findMaximumRights($roles, $node);
    }

    /**
     * @deprecated no replacement
     */
    public function getRights(ResourceNode $resourceNode): array
    {
        return array_map(function (ResourceRights $rights) {
            return $this->serializer->serialize($rights);
        }, $resourceNode->getRights()->toArray());
    }

    /**
     * Checks if the current user is a manager of a resource.
     *
     * A user is a manager of a resource if:
     *   - It is the creator of the resource
     *   - It is the manager of the parent workspace
     *   - It is a platform admin
     *
     * @deprecated should use AuthorizationChecker::isGranted('ADMINISTRATE', $resourceNode)
     */
    public function isManager(ResourceNode $resourceNode): bool
    {
        $token = $this->tokenStorage->getToken();

        if (!$token?->getUser() instanceof User) {
            return false;
        }

        $roleNames = $token?->getRoleNames() ?? [];
        if (in_array('ROLE_ADMIN', $roleNames)) {
            return true;
        }

        // if not workspace usurper
        if ($token->getUser() === $resourceNode->getCreator() && !$this->workspaceManager->isImpersonated($token)) {
            return true;
        }

        $workspace = $resourceNode->getWorkspace();

        // if we manage the workspace
        if ($workspace && $this->workspaceManager->isManager($workspace, $token)) {
            return true;
        }

        return false;
    }

    public function getCurrentPermissionArray(ResourceNode $resourceNode): array
    {
        $roleNames = $this->tokenStorage->getToken()?->getRoleNames() ?? [PlatformRoles::ANONYMOUS];

        $creatable = [];
        if ($this->isManager($resourceNode)) {
            /** @var ResourceType[] $resourceTypes */
            $resourceTypes = $this->om->getRepository(ResourceType::class)->findAll();

            foreach ($resourceTypes as $resourceType) {
                $creatable[] = $resourceType->getName();
            }

            $actions = [];
            foreach ($this->maskManager->getDecoders($resourceNode->getResourceType()) as $decoder) {
                $actions[] = $decoder->getName();
            }

            $perms = array_fill_keys($actions, true);
        } else {
            $creatable = $this->getCreatableTypes($roleNames, $resourceNode);

            $perms = $this->maskManager->decodeMask(
                $this->rightsRepo->findMaximumRights($roleNames, $resourceNode),
                $resourceNode->getResourceType()
            );
        }

        return array_merge($perms, ['create' => $creatable]);
    }

    private function getCreatableTypes(array $roles, ResourceNode $node): array
    {
        return $this->rightsRepo->findCreationRights($roles, $node);
    }

    private function singleUpdate(ResourceNode $node, Role $role, ?int $mask = 1, ?array $types = []): void
    {
        $creationTypes = '';
        if (!empty($types)) {
            $allTypes = $this->om->getRepository(ResourceType::class)->findAll();
            $creationTypes = json_encode(array_reduce($allTypes, function (array $acc, ResourceType $type) use ($types) {
                if (in_array($type->getName(), $types)) {
                    $acc[] = $type->getName();
                }

                return $acc;
            }, []), true);
        }

        if (!empty($creationTypes)) {
            $sql = "
                INSERT INTO claro_resource_rights (role_id, mask, resourceNode_id, creatableTypes)
                VALUES ({$role->getId()}, $mask, {$node->getId()}, '$creationTypes')
                ON DUPLICATE KEY UPDATE mask = $mask, creatableTypes = '$creationTypes'
            ";
        } else {
            $sql = "
                INSERT INTO claro_resource_rights (role_id, mask, resourceNode_id, creatableTypes)
                VALUES ({$role->getId()}, $mask, {$node->getId()}, NULL)
                ON DUPLICATE KEY UPDATE mask = $mask, creatableTypes = NULL
            ";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->executeQuery();
    }

    private function recursiveUpdate(ResourceNode $node, Role $role, ?int $mask = 1, ?array $types = []): void
    {
        // take into account the fact that some node have type with extended permissions
        // default actions should be set in stone with that way of doing it
        $fullDirectoryMask = pow(2, count(MaskDecoder::DEFAULT_ACTIONS)) - 1;

        $creationTypes = '';
        if (!empty($types)) {
            $allTypes = $this->om->getRepository(ResourceType::class)->findAll();
            $creationTypes = json_encode(array_reduce($allTypes, function (array $acc, ResourceType $type) use ($types) {
                if (in_array($type->getName(), $types)) {
                    $acc[] = $type->getName();
                }

                return $acc;
            }, []), true);
        }

        /*
         * For complexes resources the bits look like this.
         *
         * common      | custom
         * 1 1 0 1 1 0 | 1 1
         *
         * We only want to change the first part
         * How do we do that ?
         * First we reset the common part with the bitwise NOT (~) operator because we know the full common mask.
         * Then we use the bitwise AND (&) operator
         *
         * the php equivalent would be
         *  newMask | oldMask &~ $fullDirectoryMask
         */
        if (!empty($creationTypes)) {
            $sql = "
                INSERT INTO claro_resource_rights (role_id, mask, resourceNode_id, creatableTypes)
                SELECT {$role->getId()}, {$mask}, node.id, '$creationTypes' AS creatableTypes
                FROM claro_resource_node node
                WHERE node.path LIKE ?
                ON DUPLICATE KEY UPDATE mask = {$mask} | mask &~ {$fullDirectoryMask}, creatableTypes = '$creationTypes';
            ";
        } else {
            $sql = "
                INSERT INTO claro_resource_rights (role_id, mask, resourceNode_id, creatableTypes)
                SELECT {$role->getId()}, {$mask}, node.id, NULL AS creatableTypes 
                FROM claro_resource_node node
                WHERE node.path LIKE ?
                ON DUPLICATE KEY UPDATE mask = {$mask} | mask &~ {$fullDirectoryMask}, creatableTypes = NULL;
            ";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $node->getPath().'%');
        $stmt->executeQuery();
    }
}
