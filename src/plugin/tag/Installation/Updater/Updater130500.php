<?php

namespace Claroline\TagBundle\Installation\Updater;

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
        // remove admin tool (moved on desktop)
        $tagsTool = $this->om->getRepository(AdminTool::class)->findOneBy([
            'name' => 'claroline_tag_admin_tool',
        ]);

        if ($tagsTool) {
            $this->om->remove($tagsTool);
            $this->om->flush();
        }
    }
}
