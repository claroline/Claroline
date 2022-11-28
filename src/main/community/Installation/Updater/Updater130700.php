<?php

namespace Claroline\CommunityBundle\Installation\Updater;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Plugin;
use Claroline\CoreBundle\Entity\Tool\AdminTool;
use Claroline\InstallationBundle\Updater\Updater;

class Updater130700 extends Updater
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
        // remove community admin tool (replaced by desktop tool)
        $adminTool = $this->om->getRepository(AdminTool::class)->findOneBy([
            'name' => 'community',
        ]);

        if ($adminTool) {
            // let's cascades remove all related records
            $this->om->remove($adminTool);
            $this->om->flush();
        }

        // remove team plugin
        $plugin = $this->om->getRepository(Plugin::class)->findOneBy([
            'vendorName' => 'Claroline',
            'bundleName' => 'TeamBundle',
        ]);

        if ($plugin) {
            $this->om->remove($plugin);
        }
    }
}
