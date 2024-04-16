<?php

namespace Claroline\CursusBundle\Installation\Updater;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Component\Context\PublicContext;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\InstallationBundle\Updater\Updater;

class Updater142000 extends Updater
{
    public function __construct(private readonly ObjectManager $om)
    {
    }

    public function postUpdate(): void
    {
        $this->createPublicTools();
    }

    private function createPublicTools(): void
    {
        $orderedTool = new OrderedTool();
        $orderedTool->setContextName(PublicContext::getName());
        $orderedTool->setName('presence');

        $this->om->persist($orderedTool);
        $this->om->flush();
    }
}
