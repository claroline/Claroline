<?php

namespace Innova\CollecticielBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller as BaseInstaller;
use Innova\CollecticielBundle\DataFixtures\DefaultData;

class AdditionalInstaller extends BaseInstaller
{
    public function postInstall()
    {
        $om = $this->container->get('claroline.persistence.object_manager');
        // load plugin default data
        $default = new DefaultData();
        $default->load($om);
    }
}
