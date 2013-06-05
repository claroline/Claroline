<?php

namespace ICAP\BlogBundle;

use Claroline\CoreBundle\Library\PluginBundle;

class ICAPBlogBundle extends PluginBundle
{
    public function getRoutingPrefix()
    {
        return "icap_blog";
    }
}