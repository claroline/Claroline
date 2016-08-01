<?php

namespace Claroline\ChatBundle\Library\Xmpp;

use Fabiang\Xmpp\EventListener\Stream\Bind;
use Fabiang\Xmpp\EventListener\Stream\Roster;
use Fabiang\Xmpp\EventListener\Stream\Session;
use Fabiang\Xmpp\EventListener\Stream\StartTls;
use Fabiang\Xmpp\EventListener\Stream\Stream;
use Fabiang\Xmpp\EventListener\Stream\StreamError;
use Fabiang\Xmpp\Protocol\DefaultImplementation;

/**
 * Default Protocol implementation.
 */
class AnonymousImplementation extends DefaultImplementation
{
    public function register($ssl = false)
    {
        $this->registerListener(new Stream());
        $this->registerListener(new StreamError());
        if ($ssl) {
            $this->registerListener(new StartTls());
        }
        $this->registerListener(new Bind());
        $this->registerListener(new Session());
        $this->registerListener(new Roster());
        $this->registerListener(new Listener\Register());
    }
}
