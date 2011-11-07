<?php

namespace Claroline\WorkspaceBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ClarolineWorkspaceBundle extends Bundle
{
    public function getInstallationIndex()
    {
        return 6;
    }
}