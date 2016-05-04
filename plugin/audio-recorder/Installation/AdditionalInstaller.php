<?php

namespace Innova\AudioRecorderBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller as BaseInstaller;
use Innova\AudioRecorderBundle\DataFixtures\DefaultData;

class AdditionalInstaller extends BaseInstaller
{
    public function postInstall()
    {
        $default = new DefaultData();
        $default->load($this->container->get('claroline.persistence.object_manager'));
    }
}
