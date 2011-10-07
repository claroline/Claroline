<?php

namespace Invalid\UnexpectedRoutingPrefix2;

use Claroline\PluginBundle\AbstractType\ClarolinePlugin;

class InvalidUnexpectedRoutingPrefix2 extends ClarolinePlugin
{
    public function getPrefix()
    {
        return '';
    }
}