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

use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater040800 extends Updater
{
    private $connection;
    private $container;

    /**
     * @var \Claroline\CoreBundle\Manager\ToolManager
     */
    private $toolManager;

    public function __construct(ContainerInterface $container)
    {
        $this->connection = $container->get('doctrine.dbal.default_connection');
        $this->container = $container;
        $this->toolManager = $container->get('claroline.manager.tool_manager');
    }

    public function postUpdate()
    {
        $this->createMessageDesktopTool();
        $this->updateHomeTabsAdminTool();
        $this->updateWorkspaceMaxUsers();
    }

    public function preUpdate()
    {
        $this->deleteDuplicatedOrderedTools();
    }

    private function createMessageDesktopTool()
    {
        $this->log('Creating message tool...');
        $tool = $this->toolManager->getOneToolByName('message');

        if (is_null($tool)) {
            $tool = new Tool();
            $tool->setName('message');
            $tool->setClass('envelope');
            $tool->setDisplayableInWorkspace(false);
            $tool->setDisplayableInDesktop(true);
            $this->toolManager->create($tool);
            $this->createMessageDesktopOrderedTools($tool);
        }
    }

    private function createMessageDesktopOrderedTools(Tool $tool)
    {
        $this->log('Creating message ordered tools for all users...');
        $this->toolManager->createOrderedToolByToolForAllUsers($this->logger, $tool);
    }

    private function updateHomeTabsAdminTool()
    {
        $this->log('Updating home tabs admin tool...');
        $homeTabAdminTool = $this->toolManager->getAdminToolByName('home_tabs');
        $desktopAdminTool = $this->toolManager
            ->getAdminToolByName('desktop_and_home');

        if (!is_null($homeTabAdminTool) && is_null($desktopAdminTool)) {
            $homeTabAdminTool->setName('desktop_and_home');
            $homeTabAdminTool->setClass('home');
            $this->toolManager->persistAdminTool($homeTabAdminTool);
        }
    }

    private function deleteDuplicatedOrderedTools()
    {
        $this->log('Deleting duplicated ordered tools...');
        $idsToRemove = array();
        $exitingUsers = array();
        $exitingWorkspaces = array();
        $desktopSelect = '
            SELECT ot1.*
            FROM claro_ordered_tool ot1
            WHERE ot1.user_id IS NOT NULL
            AND EXISTS (
                SELECT ot2.*
                FROM claro_ordered_tool ot2
                WHERE ot1.tool_id = ot2.tool_id
                AND ot1.user_id = ot2.user_id
            )
            ORDER BY ot1.id ASC
        ';
        $desktopRows = $this->connection->query($desktopSelect);

        foreach ($desktopRows as $ot) {
            $toolId = $ot['tool_id'];
            $userId = $ot['user_id'];

            if (isset($exitingUsers[$toolId])) {
                if (isset($exitingUsers[$toolId][$userId])) {
                    $idsToRemove[] = $ot['id'];
                } else {
                    $exitingUsers[$toolId][$userId] = true;
                }
            } else {
                $exitingUsers[$toolId] = array();
                $exitingUsers[$toolId][$userId] = true;
            }
        }

        $workspaceSelect = '
            SELECT ot1.*
            FROM claro_ordered_tool ot1
            WHERE ot1.workspace_id IS NOT NULL
            AND EXISTS (
                SELECT ot2.*
                FROM claro_ordered_tool ot2
                WHERE ot1.tool_id = ot2.tool_id
                AND ot1.workspace_id = ot2.workspace_id
            )
            ORDER BY ot1.id
        ';
        $workspaceRows = $this->connection->query($workspaceSelect);

        foreach ($workspaceRows as $ot) {
            $toolId = $ot['tool_id'];
            $workspaceId = $ot['workspace_id'];

            if (isset($exitingWorkspaces[$toolId])) {
                if (isset($exitingWorkspaces[$toolId][$workspaceId])) {
                    $idsToRemove[] = $ot['id'];
                } else {
                    $exitingWorkspaces[$toolId][$workspaceId] = true;
                }
            } else {
                $exitingWorkspaces[$toolId] = array();
                $exitingWorkspaces[$toolId][$workspaceId] = true;
            }
        }

        if (count($idsToRemove) > 0) {
            $deleteReq = '
                DELETE FROM claro_ordered_tool
                WHERE id IN (';

            for ($i = 0; $i < count($idsToRemove); ++$i) {
                if ($i < count($idsToRemove) - 1) {
                    $deleteReq .= $idsToRemove[$i].',';
                } else {
                    $deleteReq .= $idsToRemove[$i];
                }
            }
            $deleteReq .= ')';
            $this->connection->query($deleteReq);
        }
    }

    private function updateWorkspaceMaxUsers()
    {
        $this->log('Updating workspace users limit...');
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');
        /** @var \Claroline\CoreBundle\Repository\WorkspaceRepository $wsRepo */
        $wsRepo = $em->getRepository('ClarolineCoreBundle:Workspace\Workspace');
        $workspacesQuery = $wsRepo->createQueryBuilder('workspace')->getQuery();
        $i = 0;
        $workspaces = $workspacesQuery->iterate();
        foreach ($workspaces as $row) {
            $workspace = $row[0];
            $workspace->setMaxUsers(10000);
            $em->persist($workspace);

            if ($i % 200 === 0) {
                $this->log('    200 workspace updated...');
                $em->flush();
                $em->clear();
            }

            ++$i;
        }
        $em->flush();
    }
}
