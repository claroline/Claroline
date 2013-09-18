<?php

namespace ICAP\WikiBundle;

use Claroline\CoreBundle\Library\PluginBundle;

class ICAPWikiBundle extends PluginBundle
{
    public function getRoutingPrefix()
    {
        return "wiki";
    }


}