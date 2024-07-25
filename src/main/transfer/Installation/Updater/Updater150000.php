<?php

namespace Claroline\TransferBundle\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;
use Doctrine\DBAL\Connection;

class Updater150000 extends Updater
{
    public function __construct(
        private readonly Connection $connection
    ) {
    }

    public function postUpdate(): void
    {
        $deleteTool = $this->connection->prepare(
            'DELETE FROM claro_tools WHERE name = "transfer"'
        );
        $deleteTool->executeQuery();

        $deleteOrderedTool = $this->connection->prepare(
            'DELETE FROM claro_ordered_tool WHERE tool_name = "transfer"'
        );
        $deleteOrderedTool->executeQuery();
    }
}
