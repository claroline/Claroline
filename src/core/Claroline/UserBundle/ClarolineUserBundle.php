<?php

namespace Claroline\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ClarolineUserBundle extends Bundle
{
    public function getInstallationIndex()
    {
        return 4;
    }
}
