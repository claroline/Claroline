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

use Claroline\CoreBundle\Entity\Tool\ToolMaskDecoder;
use Claroline\InstallationBundle\Updater\Updater;
use Doctrine\DBAL\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater030800 extends Updater
{
    /** @var  Connection */
    private $connection;
    private $container;
    private $maskManager;
    private $om;
    private $orderedToolRepo;
    private $roleManager;
    private $toolRepo;
    private $toolRightsManager;

    public function __construct(ContainerInterface $container)
    {
        $this->connection = $container->get('doctrine.dbal.default_connection');
        $this->container = $container;
        $this->maskManager =
            $container->get('claroline.manager.tool_mask_decoder_manager');
        $this->om = $container->get('claroline.persistence.object_manager');
        $this->orderedToolRepo =
            $this->om->getRepository('ClarolineCoreBundle:Tool\OrderedTool');
        $this->roleManager = $container->get('claroline.manager.role_manager');
        $this->toolRepo = $this->om->getRepository('ClarolineCoreBundle:Tool\Tool');
        $this->toolRightsManager =
            $container->get('claroline.manager.tool_rights_manager');
        $this->eventRepo =
            $this->om->getRepository('ClarolineCoreBundle:Event');
    }

    public function preUpdate()
    {
        $this->orderedToolsRolesTableBackup();
    }

    public function postUpdate()
    {
        $this->om->startFlushSuite();
        $this->createDefaultToolMaskDecoders();
        $this->updateToolsRights();
        $this->om->endFlushSuite();
        $this->deleteBackupTable();
        $this->emptyOrderedToolRoleTable();
        $this->refreshEvents();
        $this->createHomeMangagerRole();
    }

    private function createDefaultToolMaskDecoders()
    {
        $this->log('Creating default tool mask decoders...');
        $this->om->startFlushSuite();
        $tools = $this->toolRepo->findToolsDispayableInWorkspace();

        foreach ($tools as $tool) {
            $maskDecoders = $this->maskManager->getMaskDecodersByTool($tool);

            if (count($maskDecoders) === 0) {
                $this->maskManager->createDefaultToolMaskDecoders($tool);
            }
        }
        $this->om->endFlushSuite();
    }

    private function updateToolsRights()
    {
        $this->log('Updating tool rights...');

        $query = 'SELECT * FROM claro_ordered_tool_role_temp';
        $rows = $this->connection->query($query);
        $value = ToolMaskDecoder::$defaultValues['open'];
        $count = 0;
        $insertQuery = '
            INSERT INTO claro_tool_rights (role_id, ordered_tool_id, mask)
            VALUES
        ';

        foreach ($rows as $row) {
            $rights = $this->toolRightsManager->getRightsByRoleIdAndOrderedToolId(
                $row['role_id'],
                $row['orderedtool_id']
            );

            if (is_null($rights)) {
                if ($count === 0) {
                    $insertQuery .= "({$row['role_id']}, {$row['orderedtool_id']}, {$value})";
                } else {
                    $insertQuery .= ", ({$row['role_id']}, {$row['orderedtool_id']}, {$value})";
                }
                ++$count;
            }
        }

        if ($count > 0) {
            $this->connection->query($insertQuery);
        }
    }

    private function orderedToolsRolesTableBackup()
    {
        $tablesList = $this->connection->getSchemaManager()->listTableNames();

        if (!in_array('claro_ordered_tool_role_temp', $tablesList)) {
            $this->log('backing up claro_ordered_tool_role table...');

            $query = '
                CREATE TABLE claro_ordered_tool_role_temp
                AS (SELECT * FROM claro_ordered_tool_role)
            ';
            $this->connection->query($query);
        } else {
            $this->log('claro_ordered_tool_role_temp talbe already exists');
        }
    }

    private function deleteBackupTable()
    {
        $this->log('deleting temporary table...');
        $this->connection->query('DROP TABLE claro_ordered_tool_role_temp');
    }

    private function emptyOrderedToolRoleTable()
    {
        $this->log('emptying claro_ordered_tool_role table...');
        $this->connection->query('SET FOREIGN_KEY_CHECKS=0');
        $this->connection->query('TRUNCATE TABLE claro_ordered_tool_role');
        $this->connection->query('SET FOREIGN_KEY_CHECKS=1');
    }

    private function refreshEvents()
    {
        $this->log('Updating events...');
        $events = $this->eventRepo->findAll();

        //there shouldn't be too much events atm
        foreach ($events as $event) {
            $isAllDay = $event->getAllDay();
            //$isAllDay = 1;
            $event->setIsTask($isAllDay);
            $event->setAllDay(false);
            $this->om->persist($event);

            $this->om->flush();
        }
    }

    private function createHomeMangagerRole()
    {
        $this->log('Creating home manager role...');
        $name = 'ROLE_HOME_MANAGER';
        $key = 'home_manager';
        $role = $this->roleManager->getRoleByName($name);

        if (is_null($role)) {
            $this->roleManager->createBaseRole($name, $key);
        }
    }
}
