<?php

namespace Icap\NotificationBundle\Installation\Updater;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Tool\Tool;
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
        // remove notification tool (moved in account section)
        $tool = $this->om->getRepository(Tool::class)->findOneBy([
            'name' => 'notification',
        ]);

        if ($tool) {
            // let's cascades remove all related records
            $this->om->remove($tool);
            $this->om->flush();
        }
    }
}
