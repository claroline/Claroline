<?php

namespace UJM\ExoBundle\Installation\Updater;

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
        // remove question bank tool (moved in quiz editor)
        $bankTool = $this->om->getRepository(Tool::class)->findOneBy([
            'name' => 'ujm_questions',
        ]);

        if ($bankTool) {
            // let's cascades remove all related records
            $this->om->remove($bankTool);
            $this->om->flush();
        }
    }
}
