<?php

namespace ICAP\LessonBundle;

use Claroline\CoreBundle\Library\PluginBundle;

class ICAPLessonBundle extends PluginBundle
{
    public function getRoutingPrefix()
    {
        return "lesson";
    }
}