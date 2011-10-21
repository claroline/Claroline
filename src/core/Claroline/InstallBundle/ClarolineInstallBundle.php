<?php

namespace Claroline\InstallBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ClarolineInstallBundle extends Bundle
{
    public function getInstallationIndex()
    {
        return 1;
    }
}