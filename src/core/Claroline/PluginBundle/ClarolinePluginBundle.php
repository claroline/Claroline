<?php

namespace Claroline\PluginBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ClarolinePluginBundle extends Bundle
{
    public function getInstallationIndex()
    {
        return 6;
    }
}