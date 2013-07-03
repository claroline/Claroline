<?php

namespace Claroline\CoreBundle\Library\EventDispatcher;

use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Symfony\Component\EventDispatcher\Event;

class EventDispatcher extends ContainerAwareEventDispatcher
{
    /**
     * {@inheritDoc}
     *
     * Throw exception if no listener is attach to the event name.
     *
     * @throws \InvalidArgumentException if the service is not defined
     */
    public function dispatch($eventName, Event $event = null)
    {
        if(!$this->hasListeners($eventName))
        {
            throw new \Exception(sprintf("No listener attached to the `%s` event.", $eventName));
        }

        return parent::dispatch($eventName, $event);
    }
}
