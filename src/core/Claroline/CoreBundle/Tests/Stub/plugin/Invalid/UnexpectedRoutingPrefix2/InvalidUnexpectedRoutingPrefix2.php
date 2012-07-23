<?php

namespace Invalid\UnexpectedRoutingPrefix2;

use Claroline\CoreBundle\Library\PluginBundle;

class InvalidUnexpectedRoutingPrefix2 extends PluginBundle
{
    public function getRoutingPrefix()
    {
        return '';
    }
}