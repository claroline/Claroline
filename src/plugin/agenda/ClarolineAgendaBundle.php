<?php

namespace Claroline\AgendaBundle;

use Claroline\AgendaBundle\Installation\AdditionalInstaller;
use Claroline\KernelBundle\Bundle\DistributionPluginBundle;

class ClarolineAgendaBundle extends DistributionPluginBundle
{
    public function getAdditionalInstaller()
    {
        return new AdditionalInstaller($this->getUpdaterServiceLocator());
    }

    public function getRequiredFixturesDirectory(string $environment): string
    {
        return 'DataFixtures/Required';
    }
}
