<?php

namespace Invalid\UnexpectedRoutingPrefix1;

use Claroline\PluginBundle\AbstractType\ClarolinePlugin;

class InvalidUnexpectedRoutingPrefix1 extends ClarolinePlugin
{
    public function getRoutingPrefix()
    {
        return array();
    }
}