<?php

namespace Invalid\UnexpectedRoutingPrefix1;

use Claroline\CoreBundle\Library\PluginBundle;

class InvalidUnexpectedRoutingPrefix1 extends PluginBundle
{
    public function getRoutingPrefix()
    {
        return array();
    }
}