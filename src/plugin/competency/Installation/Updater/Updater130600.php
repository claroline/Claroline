<?php

namespace HeVinci\CompetencyBundle\Installation\Updater;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\InstallationBundle\Updater\Updater;

class Updater130600 extends Updater
{
    /** @var ObjectManager */
    private $om;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    public function postUpdate()
    {
        // remove objective tool
        $objectiveTool = $this->om->getRepository(Tool::class)->findOneBy([
            'name' => 'my-learning-objectives',
        ]);

        if ($objectiveTool) {
            // let's cascades remove all related records
            $this->om->remove($objectiveTool);
            $this->om->flush();
        }
    }
}
