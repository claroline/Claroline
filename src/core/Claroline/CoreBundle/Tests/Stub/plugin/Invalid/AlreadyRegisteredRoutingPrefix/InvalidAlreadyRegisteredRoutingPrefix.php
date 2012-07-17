<?php

namespace Invalid\AlreadyRegisteredRoutingPrefix;

use Claroline\CoreBundle\Library\PluginBundle;

class InvalidAlreadyRegisteredRoutingPrefix extends PluginBundle
{
    public function getRoutingPrefix()
    {
        // this prefix is already used by the core routing
        return 'admin';
    }
}