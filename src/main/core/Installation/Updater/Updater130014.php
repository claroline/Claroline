<?php

namespace Claroline\CoreBundle\Installation\Updater;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\Tool\ToolMaskDecoder;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\InstallationBundle\Updater\Updater;
use Doctrine\DBAL\Connection;

class Updater130014 extends Updater
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

        // generate right

        // generate creation list
    }
}
