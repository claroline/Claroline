<?php

namespace Claroline\AdminBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ClarolineAdminBundle extends Bundle
{
    public function getInstallationIndex()
    {
        return 10;
    }
}