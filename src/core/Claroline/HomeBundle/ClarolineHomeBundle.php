<?php

namespace Claroline\HomeBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ClarolineHomeBundle extends Bundle
{
    public function getInstallationIndex()
    {
        return 8;
    }
}