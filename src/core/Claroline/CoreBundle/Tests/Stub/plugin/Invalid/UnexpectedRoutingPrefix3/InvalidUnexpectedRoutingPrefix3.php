<?php

namespace Invalid\UnexpectedRoutingPrefix3;

use Claroline\CoreBundle\Library\PluginBundle;

class InvalidUnexpectedRoutingPrefix3 extends PluginBundle
{
    public function getRoutingPrefix()
    {
        return "\rInvalid\trouting\n prefix";
    }
}