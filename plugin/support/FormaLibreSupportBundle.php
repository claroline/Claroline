<?php

namespace FormaLibre\SupportBundle;

use Claroline\CoreBundle\Library\DistributionPluginBundle;
use FormaLibre\SupportBundle\Library\Installation\AdditionalInstaller;

class FormaLibreSupportBundle extends DistributionPluginBundle
{
    public function hasMigrations()
    {
        return true;
    }

    public function getAdditionalInstaller()
    {
        return new AdditionalInstaller();
    }

    public function getRequiredFixturesDirectory($environment)
    {
        return 'DataFixtures';
    }

    public function getRequiredPlugins()
    {
        return ['Claroline\\MessageBundle\\ClarolineMessageBundle'];
    }
}
