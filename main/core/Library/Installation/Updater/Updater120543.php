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

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\Tool\ToolRights;
use Claroline\InstallationBundle\Updater\Updater;
use Doctrine\DBAL\Driver\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater120543 extends Updater
{
    /** @var Connection */
    private $conn;

    /** @var ObjectManager */
    private $om;

    public function __construct(ContainerInterface $container, $logger = null)
    {
        $this->logger = $logger;
        $this->conn = $container->get('doctrine.dbal.default_connection');
        $this->om = $container->get(ObjectManager::class);
    }

    public function postUpdate()
    {
        $this->migrateDesktopToolsRights();
        $this->addMissingDesktopRights();
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

    private function addMissingDesktopRights()
    {
        $this->log('Add missing desktop tools rights...');

        /** @var Role[] $platformRoles */
        $platformRoles = $this->om->getRepository(Role::class)->findAllPlatformRoles();
        /** @var OrderedTool[] $orderedTools */
        $orderedTools = $this->om->getRepository(OrderedTool::class)->findByDesktop();
        foreach ($orderedTools as $orderedTool) {
            $results = $this->conn
                ->query("SELECT * from claro_tools_role WHERE tool_id = {$orderedTool->getTool()->getId()}")
                ->fetchAll();

            foreach ($platformRoles as $platformRole) {
                foreach ($orderedTool->getRights() as $right) {
                    if ($right->getRole()->getId() === $platformRole->getId()) {
                        // nothing to do
                        break 2;
                    }
                }

                $found = false;
                foreach ($results as $result) {
                    if ($platformRole->getId() === $result['role_id']) {
                        $found = true;
                        break;
                    }
                }

                if (!$found) {
                    $rights = new ToolRights();
                    $rights->setMask(1);
                    $rights->setRole($platformRole);
                    $rights->setOrderedTool($orderedTool);

                    $orderedTool->addRight($rights);

                    $this->om->persist($rights);
                }
            }

            $this->om->persist($orderedTool);
        }

        $this->om->flush();
    }
}
