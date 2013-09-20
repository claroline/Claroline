<?php

namespace Icap\LessonBundle;

use Claroline\CoreBundle\Library\PluginBundle;

class IcapLessonBundle extends PluginBundle
{
    public function getRoutingPrefix()
    {
        return "lesson";
    }
}