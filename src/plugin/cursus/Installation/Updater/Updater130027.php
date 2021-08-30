<?php

namespace Claroline\CursusBundle\Installation\Updater;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CursusBundle\DataFixtures\PostInstall\LoadTemplateData;
use Claroline\InstallationBundle\Updater\Updater;

class Updater130027 extends Updater
{
    private $om;
    private $dataFixtures;

    public function __construct(
        ObjectManager $om,
        LoadTemplateData $dataFixtures
    ) {
        $this->om = $om;
        $this->dataFixtures = $dataFixtures;
    }

    public function postUpdate()
    {
        $this->dataFixtures->load($this->om);
    }
}
