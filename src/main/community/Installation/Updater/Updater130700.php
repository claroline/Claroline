<?php

namespace Claroline\CommunityBundle\Installation\Updater;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Plugin;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\AdminTool;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Manager\Tool\ToolManager;
use Claroline\InstallationBundle\Updater\Updater;

class Updater130700 extends Updater
{
    /** @var ObjectManager */
    private $om;
    /** @var ToolManager */
    private $toolManager;

    public function __construct(
        ObjectManager $om,
        ToolManager $toolManager
    ) {
        $this->om = $om;
        $this->toolManager = $toolManager;
    }

    public function postUpdate(): void
    {
        $this->removeCommunityAdminTool();

        // give all the rights to the community to organization administrators
        $orderedTool = $this->toolManager->getOrderedTool('community', Tool::DESKTOP);
        if ($orderedTool) {
            $adminOrganization = $this->om->getRepository(Role::class)->findOneBy(['name' => 'ROLE_ADMIN_ORGANIZATION']);
            if ($adminOrganization) {
                $this->toolManager->setPermissions([
                    'open' => true,
                    'edit' => true,
                    'administrate' => true,
                    'delete' => true,
                    'create_user' => true,
                ], $orderedTool, $adminOrganization);
            }
        }

        $this->removeTeamPlugin();
    }

    private function removeCommunityAdminTool(): void
    {
        // remove community admin tool (replaced by desktop tool)
        $adminTool = $this->om->getRepository(AdminTool::class)->findOneBy([
            'name' => 'community',
        ]);

        if ($adminTool) {
            // let's cascades remove all related records
            $this->om->remove($adminTool);
            $this->om->flush();
        }
    }

    private function removeTeamPlugin(): void
    {
        // remove team plugin (merged with community)
        $plugin = $this->om->getRepository(Plugin::class)->findOneBy([
            'vendorName' => 'Claroline',
            'bundleName' => 'TeamBundle',
        ]);

        if ($plugin) {
            $this->om->remove($plugin);
            $this->om->flush();
        }
    }
}
