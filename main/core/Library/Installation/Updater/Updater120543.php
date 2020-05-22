<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;
use Doctrine\DBAL\Driver\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater120543 extends Updater
{
    /** @var Connection */
    private $conn;

    public function __construct(ContainerInterface $container, $logger = null)
    {
        $this->logger = $logger;
        $this->conn = $container->get('doctrine.dbal.default_connection');
    }

    public function postUpdate()
    {
        $this->migrateDesktopToolsRights();
    }

    private function migrateDesktopToolsRights()
    {
        $this->log('Migrate desktop tools rights...');

        // create rights for those who can access the tool
        $this->conn
            ->prepare('
                INSERT INTO claro_tool_rights (role_id, mask, ordered_tool_id) 
                SELECT role_id, 1, ot.id 
                FROM claro_tools_role AS tr
                JOIN claro_tools AS t ON tr.tool_id = t.id
                JOIN claro_ordered_tool AS ot ON (t.id = ot.tool_id AND ot.user_id IS NULL AND ot.workspace_id IS NULL)
                WHERE tr.display IS NULL OR tr.display = "forced"
            ')
            ->execute();

        // create rights for those who cannot access the tool
        $this->conn
            ->prepare('
                INSERT INTO claro_tool_rights (role_id, mask, ordered_tool_id) 
                SELECT role_id, 0, ot.id 
                FROM claro_tools_role AS tr
                JOIN claro_tools AS t ON tr.tool_id = t.id
                JOIN claro_ordered_tool AS ot ON (t.id = ot.tool_id AND ot.user_id IS NULL AND ot.workspace_id IS NULL)
                WHERE tr.display = "hidden"
            ')
            ->execute();
    }
}
