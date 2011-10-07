<?php

namespace Invalid\UnexpectedRoutingPrefix3;

use Claroline\PluginBundle\AbstractType\ClarolinePlugin;

class InvalidUnexpectedRoutingPrefix3 extends ClarolinePlugin
{
    public function getPrefix()
    {
        return "\rInvalid\trouting\n prefix";
    }
}