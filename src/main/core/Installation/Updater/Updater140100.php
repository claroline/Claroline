<?php

namespace Claroline\CoreBundle\Installation\Updater;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Component\Context\AccountContext;
use Claroline\CoreBundle\Component\Context\AdministrationContext;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Manager\Tool\ToolManager;
use Claroline\InstallationBundle\Updater\Updater;
use Doctrine\DBAL\Connection;

class Updater140100 extends Updater
{
    public function __construct(
        private readonly Connection $connection,
        private readonly ObjectManager $om,
        private readonly ToolManager $toolManager
    ) {
    }

    public function postUpdate(): void
    {
        $this->createAdminTools();
        $this->createAccountTools();
        // create home tools
        // migrate old home config
    }

    private function createAdminTools(): void
    {
        // static list to preserve original order
        $tools = [
            'home' => 'home',
            'connection_messages' => 'connection_messages',
            'parameters' => 'main_settings',
            'privacy' => 'privacy',
            'authentication' => 'authentication',
            'plugins' => 'plugins',
            'integration' => 'integration',
            'templates' => 'templates',
            'scheduler' => 'scheduled_tasks',
            'logs' => 'logs',
        ];

        $index = 0;
        foreach ($tools as $newName => $oldName) {
            $orderedTool = new OrderedTool();
            $orderedTool->setContextName(AdministrationContext::getName());
            $orderedTool->setOrder($index);
            $orderedTool->setName($newName);

            $this->om->persist($orderedTool);

            // migrate rights
            $stmt = $this->connection->prepare('
                SELECT r.role_id 
                FROM claro_admin_tool_role AS r
                LEFT JOIN claro_admin_tools AS at ON (r.admintool_id = at.id)
                WHERE at.name = :toolName
            ');

            $configuredRoles = $stmt->executeQuery([
                'toolName' => $oldName,
            ])->fetchAllAssociative();

            foreach ($configuredRoles as $role) {
                $roleEntity = $this->om->getRepository(Role::class)->find($role['role_id']);
                if ($roleEntity) {
                    $this->toolManager->setPermissions(['open' => true, 'edit' => true, 'administrate' => true], $orderedTool, $roleEntity);
                }
            }
        }

        $this->om->flush();
    }

    private function createAccountTools(): void
    {
        // static list to preserve original order
        $tools = [
            'profile',
            'parameters',
            'privacy',
            'authentication',
            'badges',
            'notifications',
            'logs',
        ];

        foreach ($tools as $index => $tool) {
            $orderedTool = new OrderedTool();
            $orderedTool->setContextName(AccountContext::getName());
            $orderedTool->setOrder($index);
            $orderedTool->setName($tool);

            $this->om->persist($orderedTool);
        }

        $this->om->flush();
    }
}
