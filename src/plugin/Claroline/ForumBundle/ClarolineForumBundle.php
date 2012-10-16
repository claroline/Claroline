<?php

namespace Claroline\ForumBundle;

use Claroline\CoreBundle\Library\PluginBundle;

/**
 * Bundle class.
 */
class ClarolineForumBundle extends PluginBundle
{
    public function getRoutingPrefix(){
        return 'forum';
    }
}