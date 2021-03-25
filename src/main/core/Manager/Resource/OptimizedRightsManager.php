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
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerAwareInterface;

/**
 * Right manager optimizations.
 */
class OptimizedRightsManager implements LoggerAwareInterface
{
    use LoggableTrait;

    /** @var Connection */
    private $conn;

    /** @var StrictDispatcher */
    private $dispatcher;

    /** @var ObjectManager */
    private $om;

    /**
     * OptimizedRightsManager constructor.
     */
    public function __construct(Connection $conn, StrictDispatcher $dispatcher, ObjectManager $om)
    {
        $this->conn = $conn;
        $this->dispatcher = $dispatcher;
        $this->om = $om;
    }

    public function update(ResourceNode $node, Role $role, $mask = 1, $types = [], $recursive = false, $log = true)
    {
        if (!$node->getId()) {
            $this->om->save($node);
            //we really need it
            $this->om->forceFlush();
        }

        if (!$role->getId()) {
            $this->om->save($role);
            $this->om->forceFlush();
        }

        $logUpdate = true;
        $right = $this->om->getRepository(ResourceRights::class)->findOneBy(['role' => $role, 'resourceNode' => $node]);

        if ($right) {
            $logUpdate = $right->getMask() !== $mask;
        }

        $recursive ?
            $this->recursiveUpdate($node, $role, $mask, $types) :
            $this->singleUpdate($node, $role, $mask, $types);

        if ($logUpdate && $log) {
            $this->logUpdate($node, $role, $mask, $types);
        }
    }

    private function singleUpdate(ResourceNode $node, Role $role, $mask = 1, $types = [])
    {
        $sql =
          "
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
          ) as r, (
            SELECT id
            FROM claro_resource_type
            WHERE name IN
            (?)
          ) as t GROUP BY tid
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
        $sql =
          "
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
          SELECT r.id as rid, t.id as tid FROM (
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
    public function logUpdate(ResourceNode $node, Role $role, $mask, $types)
    {
        $this->dispatcher->dispatch(
            'log',
            'Log\LogWorkspaceRoleChangeRight',
            [$role, $node, [
                'mask' => $mask,
                'types' => $types,
            ]]
        );
    }
}
