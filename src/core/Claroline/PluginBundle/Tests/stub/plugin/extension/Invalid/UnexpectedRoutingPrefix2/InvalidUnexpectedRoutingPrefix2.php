<?php

namespace Invalid\UnexpectedRoutingPrefix2;

use Claroline\PluginBundle\AbstractType\ClarolineExtension;

class InvalidUnexpectedRoutingPrefix2 extends ClarolineExtension
{
    public function getRoutingPrefix()
    {
        return '';
    }
}