<?php

namespace Claroline\ResourceBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ClarolineResourceBundle extends Bundle
{
    public function getInstallationIndex()
    {
        return 7;
    }
}