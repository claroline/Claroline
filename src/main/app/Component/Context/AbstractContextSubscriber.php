<?php

namespace Claroline\AppBundle\Component\Context;

use Claroline\CoreBundle\Event\CatalogEvents\ContextEvents;
use Claroline\CoreBundle\Event\Context\AbstractContextEvent;
use Claroline\CoreBundle\Event\Context\OpenContextEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class AbstractContextSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ContextEvents::OPEN => 'open',
        ];
    }

    /**
     * Checks if the subscriber supports the context.
     */
    abstract protected static function supportsContext(string $context, ?string $contextId): bool;

    /**
     * Do something when the context is opened.
     */
    protected function onOpen(OpenContextEvent $event): void
    {
    }

    final public function __call($method, $arguments): void
    {
        $this->forwardEvent($arguments[0], 'on'.ucfirst($method));
    }

    private function forwardEvent(AbstractContextEvent $event, string $handler): void
    {
        // checks if the subscriber instance supports this context
        if (!static::supportsContext($event->getContextType(), $event->getContextId())) {
            return;
        }

        // forward event to the subscriber instance
        call_user_func([$this, $handler], $event);
    }
}
