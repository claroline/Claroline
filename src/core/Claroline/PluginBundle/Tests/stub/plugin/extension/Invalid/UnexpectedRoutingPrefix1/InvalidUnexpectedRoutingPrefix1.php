<?php

namespace Invalid\UnexpectedRoutingPrefix1;

use Claroline\PluginBundle\AbstractType\ClarolineExtension;

class InvalidUnexpectedRoutingPrefix1 extends ClarolineExtension
{
    public function getRoutingPrefix()
    {
        return array();
    }
}