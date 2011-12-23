<?php

namespace Claroline\DesktopBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ClarolineDesktopBundle extends Bundle
{
    public function getInstallationIndex()
    {
        return 9;
    }
}