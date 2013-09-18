<?php

namespace Icap\WikiBundle;

use Claroline\CoreBundle\Library\PluginBundle;

class IcapWikiBundle extends PluginBundle
{
    public function getRoutingPrefix()
    {
        return "wiki";
    }


}