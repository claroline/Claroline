<?php

namespace Claroline\TransferBundle\Installation\Updater;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Plugin;
use Claroline\CoreBundle\Entity\Tool\AdminTool;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\InstallationBundle\Updater\Updater;

class Updater130300 extends Updater
{
    /** @var ObjectManager */
    private $om;

    public function __construct(
        ObjectManager $om
    ) {
        $this->om = $om;
    }

    public function preUpdate()
    {
        // grab transfer tools from the core
        $plugin = $this->om->getRepository(Plugin::class)->findOneBy([
            'bundleName' => 'TransferBundle',
        ]);

        if (empty($plugin)) {
            return;
        }

        // move admin tool
        $adminTool = $this->om->getRepository(AdminTool::class)->findOneBy([
            'name' => 'transfer',
        ]);

        if ($adminTool) {
            $adminTool->setPlugin($plugin);
            $this->om->persist($adminTool);
        }

        // move workspace tool
        $tool = $this->om->getRepository(Tool::class)->findOneBy([
            'name' => 'transfer',
        ]);

        if ($tool) {
            $tool->setPlugin($plugin);
            $this->om->persist($tool);
        }

        $this->om->flush();
    }
}
