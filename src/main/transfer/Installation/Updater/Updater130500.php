<?php

namespace Claroline\TransferBundle\Installation\Updater;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Tool\AdminTool;
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
        // remove transfer admin tool (moved on desktop)
        $transferTool = $this->om->getRepository(AdminTool::class)->findOneBy([
            'name' => 'transfer',
        ]);

        if ($transferTool) {
            // let's cascades remove all related records
            $this->om->remove($transferTool);
            $this->om->flush();
        }
    }
}
