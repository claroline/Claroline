<?php

namespace FormaLibre\SupportBundle;

use Claroline\CoreBundle\Library\PluginBundle;

class FormaLibreSupportBundle extends PluginBundle
{
    public function hasMigrations()
    {
        return true;
    }

    public function getRequiredFixturesDirectory($environment)
    {
        return 'DataFixtures';
    }
}