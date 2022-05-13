<?php

namespace Claroline\CoreBundle\Installation\Updater;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\InstallationBundle\Updater\Updater;
use Doctrine\DBAL\Connection;

class Updater130406 extends Updater
{
    /** @var Connection */
    private $connection;

    /** @var ObjectManager */
    private $om;

    public function __construct(
        Connection $connection,
        ObjectManager $om
    ) {
        $this->connection = $connection;
        $this->om = $om;
    }

    public function postUpdate()
    {
        $this->generateToolRights();
        $this->generateResourceRights();
    }

    private function generateToolRights()
    {
        $this->log('Generate tool rights for Workspace managers...');

        // For performances reason, we will set the same mask for all tools,
        // this is the maximum rights found in platform atm, which is 31.
        // It is the mask for the tool which has the more custom actions (eg. community)
        // For other tools extra bits will just be ignored by the decoder manager so it's ok to do it.
        $mask = 31;

        /** @var Workspace[] $workspaces */
        $workspaces = $this->om->getRepository(Workspace::class)->findAll();
        foreach ($workspaces as $workspace) {
            $this->log(sprintf('Processing Workspace "%s" (%s)', $workspace->getName(), $workspace->getUuid()));

            $sql = "
                INSERT INTO claro_tool_rights (role_id, ordered_tool_id, mask)
                    (SELECT r.id, o.id, {$mask} AS mask
                    FROM (
                        SELECT r.id
                        FROM claro_role AS r
                        WHERE r.name = 'ROLE_WS_MANAGER_{$workspace->getUuid()}'
                    ) AS r, (
                        SELECT o.id 
                        FROM claro_ordered_tool AS o
                        LEFT JOIN claro_tools AS t ON (o.tool_id = t.id)
                        WHERE o.workspace_id = '{$workspace->getId()}'
                    ) AS o)
                ON DUPLICATE KEY UPDATE mask = {$mask}
            ";

            $stmt = $this->connection->prepare($sql);
            $stmt->executeQuery();
        }
    }

    private function generateResourceRights()
    {
        $this->log('Generate resource rights for Workspace managers...');

        // For performances reason, we will set the same mask for all resources,
        // this is the maximum rights found in platform atm, which is 255.
        // It is the mask for the resource which has the more custom actions (eg. quiz, blog)
        // For other resources extra bits will just be ignored by the decoder manager so it's ok to do it.
        $mask = 255;

        /** @var Workspace[] $workspaces */
        $workspaces = $this->om->getRepository(Workspace::class)->findAll();
        foreach ($workspaces as $workspace) {
            $this->log(sprintf('Processing Workspace "%s" (%s)', $workspace->getName(), $workspace->getUuid()));

            // generate right
            $sql = "
                INSERT INTO claro_resource_rights (role_id, resourceNode_id, mask)
                    (SELECT r.id, n.id, {$mask} AS mask
                    FROM (
                        SELECT r.id
                        FROM claro_role AS r
                        WHERE r.name = 'ROLE_WS_MANAGER_{$workspace->getUuid()}'
                    ) AS r, (
                        SELECT n.id 
                        FROM claro_resource_node AS n
                        WHERE n.workspace_id = '{$workspace->getId()}'
                    ) AS n)
                ON DUPLICATE KEY UPDATE mask = {$mask}
            ";

            $stmt = $this->connection->prepare($sql);
            $stmt->executeQuery();
        }

        // generate creation list
        $sql = "
            INSERT IGNORE INTO claro_list_type_creation (resource_rights_id, resource_type_id)
                (SELECT rr.id, t.id
                FROM (
                    SELECT rr.id
                    FROM claro_resource_rights AS rr
                    LEFT JOIN claro_role AS r ON (r.id = rr.role_id)
                    LEFT JOIN claro_resource_node AS n ON (n.id = rr.resourceNode_id)
                    WHERE r.name LIKE 'ROLE_WS_MANAGER_%'
                      AND n.mime_type = 'custom/directory'
                ) AS rr, (
                    SELECT t.id 
                    FROM claro_resource_type AS t
                ) AS t)
        ";

        $stmt = $this->connection->prepare($sql);
        $stmt->executeQuery();
    }
}
