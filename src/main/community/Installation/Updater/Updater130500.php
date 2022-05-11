<?php

namespace Claroline\CommunityBundle\Installation\Updater;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Plugin;
use Claroline\CoreBundle\Entity\Tool\AdminTool;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\InstallationBundle\Updater\Updater;

class Updater130500 extends Updater
{
    /** @var ObjectManager */
    private $om;

    public function __construct(
        ObjectManager $om
    ) {
        $this->om = $om;
    }

    public function postUpdate()
    {
        $corePlugin = $this->om->getRepository(Plugin::class)->findOneBy(['bundleName' => 'CoreBundle']);
        $communityPlugin = $this->om->getRepository(Plugin::class)->findOneBy(['bundleName' => 'CommunityBundle']);

        if (empty($corePlugin) || empty($communityPlugin)) {
            return;
        }

        // move community tools in the correct plugin (in order to keep the rights config)

        $coreAdmin = $this->om->getRepository(AdminTool::class)->findOneBy([
            'name' => 'community',
            'plugin' => $corePlugin,
        ]);

        if ($coreAdmin) {
            $communityAdmin = $this->om->getRepository(AdminTool::class)->findOneBy([
                'name' => 'community',
                'plugin' => $communityPlugin,
            ]);

            // remove new tool
            $this->om->remove($communityAdmin);
            // link original tool to the correct plugin
            $coreAdmin->setPlugin($communityPlugin);
        }

        $coreTool = $this->om->getRepository(Tool::class)->findOneBy([
            'name' => 'community',
            'plugin' => $corePlugin,
        ]);

        if ($coreTool) {
            $communityTool = $this->om->getRepository(Tool::class)->findOneBy([
                'name' => 'community',
                'plugin' => $communityPlugin,
            ]);

            // remove new tool
            $this->om->remove($communityTool);
            // link original tool to the correct plugin
            $coreTool->setPlugin($communityPlugin);
        }

        $this->om->flush();
    }
}
