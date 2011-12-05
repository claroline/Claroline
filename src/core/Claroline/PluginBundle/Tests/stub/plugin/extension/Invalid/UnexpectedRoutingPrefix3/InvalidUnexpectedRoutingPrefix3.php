<?php

namespace Invalid\UnexpectedRoutingPrefix3;

use Claroline\PluginBundle\AbstractType\ClarolineExtension;

class InvalidUnexpectedRoutingPrefix3 extends ClarolineExtension
{
    public function getRoutingPrefix()
    {
        return "\rInvalid\trouting\n prefix";
    }
}