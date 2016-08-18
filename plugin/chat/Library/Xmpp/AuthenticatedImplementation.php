<?php

namespace Claroline\ChatBundle\Library\Xmpp;

use Fabiang\Xmpp\EventListener\Stream\Authentication;

/**
 * Default Protocol implementation.
 */
class AuthenticatedImplementation extends AnonymousImplementation
{
    public function register()
    {
        parent::register();
        $this->registerListener(new Authentication());
    }
}
