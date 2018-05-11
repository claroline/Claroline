<?php

namespace Innova\AudioRecorderBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller as BaseInstaller;
use Innova\AudioRecorderBundle\DataFixtures\DefaultData;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class AdditionalInstaller extends BaseInstaller implements ContainerAwareInterface
{
    public function postInstall()
    {
        $default = new DefaultData();
        $default->load($this->container->get('claroline.persistence.object_manager'));
    }
}
