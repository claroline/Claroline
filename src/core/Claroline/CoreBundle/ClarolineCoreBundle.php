<?php

namespace Claroline\CoreBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ClarolineCoreBundle extends Bundle
{
    public function getInstallationIndex()
    {
        return 6;
    }
}