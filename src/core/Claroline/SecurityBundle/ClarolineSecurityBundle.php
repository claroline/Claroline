<?php

namespace Claroline\SecurityBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ClarolineSecurityBundle extends Bundle
{
    public function getInstallationIndex()
    {
        return 4;
    }
}