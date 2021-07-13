<?php

namespace Claroline\CoreBundle\Installation\Updater;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\InstallationBundle\Updater\Updater;

class Updater130025 extends Updater
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
        // remove booking tool (merged with new locations tool from core)
        $bookingTool = $this->om->getRepository(Tool::class)->findOneBy([
            'name' => 'booking',
        ]);

        if ($bookingTool) {
            // let's cascades remove all related records
            $this->om->remove($bookingTool);
            $this->om->flush();
        }
    }
}
