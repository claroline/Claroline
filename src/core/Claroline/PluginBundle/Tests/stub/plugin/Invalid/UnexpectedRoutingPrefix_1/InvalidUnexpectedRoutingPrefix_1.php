<?php

namespace Invalid\UnexpectedRoutingPrefix_1;

use Claroline\PluginBundle\AbstractType\ClarolinePlugin;

class InvalidUnexpectedRoutingPrefix_1 extends ClarolinePlugin
{
    public function getRoutingPrefix()
    {
        return array();
    }
}