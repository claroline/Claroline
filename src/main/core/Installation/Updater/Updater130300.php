<?php

namespace Claroline\CoreBundle\Installation\Updater;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Tool\AdminTool;
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

    public function postUpdate()
    {
        // remove scheduled tasks tool (moved in its own plugin)
        $taskTool = $this->om->getRepository(AdminTool::class)->findOneBy([
            'name' => 'tasks_scheduling',
        ]);

        if ($taskTool) {
            // let's cascades remove all related records
            $this->om->remove($taskTool);
            $this->om->flush();
        }
    }
}
