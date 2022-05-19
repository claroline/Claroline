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

use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Log\LoggableTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceRights;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Log\LogWorkspaceRoleChangeRightEvent;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Claroline\CoreBundle\Repository\Resource\ResourceRightsRepository;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class RightsManager implements LoggerAwareInterface
{
    use LoggableTrait;

    /** @var Connection */
    private $conn;
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var StrictDispatcher */
    private $dispatcher;
    /** @var MaskManager */
    private $maskManager;
    /** @var ResourceRightsRepository */
    private $rightsRepo;
    /** @var ObjectManager */
    private $om;

    private $container;

    public function __construct(
        Connection $conn,
        TokenStorageInterface $tokenStorage,
        StrictDispatcher $dispatcher,
        ObjectManager $om,
        MaskManager $maskManager,
        ContainerInterface $container
    ) {
        $this->conn = $conn;
        $this->tokenStorage = $tokenStorage;
        $this->dispatcher = $dispatcher;
        $this->om = $om;
        $this->maskManager = $maskManager;
        $this->container = $container; // todo remove me (required because of a circular dependency with claroline.manager.resource_manager)

        $this->rightsRepo = $om->getRepository(ResourceRights::class);
    }

    /**
     * @param array|int $permissions - either an array of perms or an encoded mask
     */
    public function create($permissions, Role $role, ResourceNode $node, bool $isRecursive, array $creations = [], bool $log = true)
    {
        $this->update($permissions, $role, $node, $isRecursive, $creations, $log);
    }

    /**
     * @param array|int $permissions - either an array of perms or an encoded mask
     */
    public function update($permissions, Role $role, ResourceNode $node, bool $isRecursive = false, array $creations = [], bool $log = true)
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

        $logUpdate = true;

        $right = $this->rightsRepo->findOneBy(['role' => $role, 'resourceNode' => $node]);
        if ($right) {
            $logUpdate = $right->getMask() !== $mask;
        }

        if ($isRecursive) {
            $this->recursiveUpdate($node, $role, $mask, $creations);
        } else {
            $this->singleUpdate($node, $role, $mask, $creations);
        }

        if ($logUpdate && $log) {
            $this->logUpdate($node, $role, $mask, $creations);
        }
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

    public function getMaximumRights(array $roles, ResourceNode $node)
    {
        return $this->rightsRepo->findMaximumRights($roles, $node);
    }

    // TODO : this should be done by a serializer
    public function getRights(ResourceNode $resourceNode): array
    {
        return array_map(function (ResourceRights $rights) use ($resourceNode) {
            $role = $rights->getRole();
            $permissions = $this->maskManager->decodeMask($rights->getMask(), $resourceNode->getResourceType());

            if ('directory' === $resourceNode->getResourceType()->getName()) {
                // ugly hack to only get create rights for directories (it's the only one that can handle it).
                $permissions = array_merge($permissions, [
                    'create' => array_map(function (ResourceType $creatableType) {
                        return $creatableType->getName();
                    }, $rights->getCreatableResourceTypes()->toArray()),
                ]);
            }

            // TODO : do not flatten role data. Use RoleSerializer instead
            $data = [
                'id' => $rights->getId(),
                'name' => $role->getName(),
                'translationKey' => $role->getTranslationKey(),
                'permissions' => $permissions,
                'workspace' => null,
            ];

            if ($role->getWorkspace()) {
                $data['workspace'] = [
                    'id' => $role->getWorkspace()->getUuid(),
                    'code' => $role->getWorkspace()->getCode(),
                    'name' => $role->getWorkspace()->getName(),
                ];
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
     */
    public function isManager(ResourceNode $resourceNode): bool
    {
        $token = $this->tokenStorage->getToken();
        $roleNames = $token->getRoleNames();

        $workspaceManager = $this->container->get(WorkspaceManager::class);

        if (!$token->getUser() instanceof User) {
            return false;
        }

        if (in_array('ROLE_ADMIN', $roleNames)) {
            return true;
        }

        // If not workspace usurper
        if ($token->getUser() === $resourceNode->getCreator() && !$workspaceManager->isImpersonated($token)) {
            return true;
        }

        $workspace = $resourceNode->getWorkspace();

        //if we manage the workspace
        if ($workspace && $workspaceManager->isManager($workspace, $token)) {
            return true;
        }

        return false;
    }

    public function getCurrentPermissionArray(ResourceNode $resourceNode): array
    {
        $roleNames = $this->tokenStorage->getToken()->getRoleNames();

        $creatable = [];
        if ($this->isManager($resourceNode)) {
            /** @var ResourceType[] $resourceTypes */
            $resourceTypes = $this->om->getRepository(ResourceType::class)->findAll();

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

    private function getCreatableTypes(array $roles, ResourceNode $node): array
    {
        $creationRights = $this->rightsRepo->findCreationRights($roles, $node);

        return array_map(function (array $type) {
            return $type['name'];
        }, $creationRights);
    }

    private function singleUpdate(ResourceNode $node, Role $role, $mask = 1, $types = [])
    {
        $sql = "
            INSERT INTO claro_resource_rights (role_id, mask, resourceNode_id)
            VALUES ({$role->getId()}, {$mask}, {$node->getId()})
            ON DUPLICATE KEY UPDATE mask = {$mask};
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $sql = "
            DELETE list FROM claro_list_type_creation list
            JOIN claro_resource_rights rights ON list.resource_rights_id = rights.id
            JOIN claro_role role ON rights.role_id = role.id
            JOIN claro_resource_node node ON rights.resourceNode_id = node.id
            WHERE node.id = {$node->getId()}
            AND role.id = {$role->getId()}
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        if (0 === count($types)) {
            return;
        }

        $typeList = array_map(function ($type) {
            return $type instanceof ResourceType ? $type->getName() : $type;
        }, $types);

        $sql = "
            INSERT INTO claro_list_type_creation (resource_rights_id, resource_type_id)
                SELECT r.id as rid, t.id as tid FROM (
                SELECT rights.id
                FROM claro_resource_rights rights
                JOIN claro_resource_node node ON rights.resourceNode_id = node.id
                JOIN claro_role role ON rights.role_id = role.id
                WHERE node.id = {$node->getId()}
                AND role.id = {$role->getId()}
            ) AS r, (
                SELECT id
                FROM claro_resource_type
                WHERE name IN
                (?)
            ) AS t GROUP BY tid
        ";

        $this->conn->executeQuery(
            $sql,
            [$typeList],
            [Connection::PARAM_STR_ARRAY]
        );
    }

    private function recursiveUpdate(ResourceNode $node, Role $role, $mask = 1, $types = [])
    {
        //take into account the fact that some node have type with extended permissions
        //default actions should be set in stone with that way of doing it
        $defaults = MaskManager::getDefaultActions();
        $fullDirectoryMask = pow(2, count($defaults)) - 1;

        /**
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
        $sql = "
            INSERT INTO claro_resource_rights (role_id, mask, resourceNode_id)
            SELECT {$role->getId()}, {$mask}, node.id FROM claro_resource_node node
            WHERE node.path LIKE ?
            ON DUPLICATE KEY UPDATE mask = {$mask} | mask &~ {$fullDirectoryMask};
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $node->getPath().'%', \PDO::PARAM_STR);
        $stmt->execute();

        $sql = "
            DELETE list FROM claro_list_type_creation list
            JOIN claro_resource_rights rights ON list.resource_rights_id = rights.id
            JOIN claro_role role ON rights.role_id = role.id
            JOIN claro_resource_node node ON rights.resourceNode_id = node.id
            WHERE node.path LIKE ?
            AND role.id = {$role->getId()}
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $node->getPath().'%', \PDO::PARAM_STR);
        $stmt->execute();

        if (0 === count($types)) {
            return;
        }

        $typeList = array_map(function ($type) {
            return $type->getName();
        }, $types);

        $sql = "
            INSERT IGNORE INTO claro_list_type_creation (resource_rights_id, resource_type_id)
                SELECT r.id as rid, t.id as tid 
                FROM (
                    SELECT rights.id
                    FROM claro_resource_rights AS rights
                    JOIN claro_resource_node AS node ON rights.resourceNode_id = node.id
                    JOIN claro_role role ON rights.role_id = role.id
                    JOIN claro_resource_type AS rType on node.resource_type_id = rType.id
                    WHERE node.path LIKE ?
                    AND role.id = {$role->getId()}
                    AND rType.name = 'directory'
                ) as r, (
                    SELECT id
                    FROM claro_resource_type
                    WHERE name IN
                    (?)
                ) as t
        ";

        $this->conn->executeQuery(
            $sql,
            [$node->getPath().'%', $typeList],
            [\PDO::PARAM_STR, Connection::PARAM_STR_ARRAY]
        );
    }

    /**
     * @param int   $mask
     * @param array $types
     */
    private function logUpdate(ResourceNode $node, Role $role, $mask, $types)
    {
        $this->dispatcher->dispatch(
            'log',
            LogWorkspaceRoleChangeRightEvent::class,
            [$role, $node, [
                'mask' => $mask,
                'types' => $types,
            ]]
        );
    }
}
