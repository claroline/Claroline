<?php

namespace Claroline\ChatBundle\Library\Xmpp\Listener;

use Fabiang\Xmpp\Event\XMLEvent;
use Fabiang\Xmpp\EventListener\AbstractEventListener;
use Fabiang\Xmpp\EventListener\BlockingEventListenerInterface;

/**
 * Listener.
 */
class Register extends AbstractEventListener implements BlockingEventListenerInterface
{
    /**
     * Blocking.
     *
     * @var bool
     */
    protected $blocking = false;

    /**
     * {@inheritdoc}
     */
    public function attachEvents()
    {
        $this->getOutputEventManager()
            ->attach('{jabber:iq:register}query', [$this, 'query']);
        $this->getInputEventManager()
            ->attach('{jabber:client}iq', [$this, 'result']);
        $this->getInputEventManager()
            ->attach('{jabber:client}error', [$this, 'result']);
    }

    /**
     * Sending a query request for roster sets listener to blocking mode.
     */
    public function query()
    {
        $this->blocking = true;
    }

    /**
     * Result received.
     *
     * @param \Fabiang\Xmpp\Event\XMLEvent $event
     */
    public function result(XMLEvent $event)
    {
        $this->blocking = false;
    }

    /**
     * {@inheritdoc}
     */
    public function isBlocking()
    {
        return $this->blocking;
    }
}
