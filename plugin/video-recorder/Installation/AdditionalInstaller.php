<?php

namespace Innova\VideoRecorderBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller as BaseInstaller;
use Innova\VideoRecorderBundle\DataFixtures\DefaultData;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class AdditionalInstaller extends BaseInstaller implements ContainerAwareInterface
{
    public function postInstall()
    {
        $om = $this->container->get('claroline.persistence.object_manager');
        // load plugin default data
        $default = new DefaultData();
        $default->load($om);
    }
}
