<?php

namespace Invalid\NonExistentResourceFile1;

use Claroline\CoreBundle\Library\PluginBundle;

class InvalidNonExistentResourceFile1 extends PluginBundle
{
    public function getCustomResourcesFile()
    {
        return 'wrong/path/file.yml';
    }
}