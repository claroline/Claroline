<?php

namespace Invalid\UnexpectedRoutingPrefix_3;

use Claroline\PluginBundle\AbstractType\ClarolinePlugin;

class InvalidUnexpectedRoutingPrefix_3 extends ClarolinePlugin
{
    public function getRoutingPrefix()
    {
        return "\rInvalid\trouting\n prefix";
    }
}