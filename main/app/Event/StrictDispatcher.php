<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AppBundle\Event;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Basic event dispatcher, wrapping the base Symfony dispatcher and adding checks
 * based on the interface of the dispatched events. It is intended to be used
 * whenever a communication between the core and the plugins is required and will
 * automatically throw an exception if this communication went wrong (i.e. if the
 * plugin didn't respond as expected).
 *
 * @DI\Service("claroline.event.event_dispatcher")
 */
class StrictDispatcher
{
    /** @var EventDispatcher */
    private $eventDispatcher;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "ed" = @DI\Inject("event_dispatcher")
     * })
     */
    public function __construct(EventDispatcherInterface $ed)
    {
        $this->eventDispatcher = $ed;
    }

    /**
     * Dispatches an event and returns its associated event object. The event object
     * is created according to the short event class name parameter, which must match
     * an event class located in the core event directory, without the first path
     * segments and the "Event" suffix.
     *
     * @param string $eventName           Name of the event
     * @param string $shortEventClassName Short name of the event class
     * @param array  $eventArgs           Parameters to be passed to the event object constructor
     *
     * @return \Symfony\Component\EventDispatcher\Event
     *
     * @throws MissingEventClassException if no event class matches the short class name
     * @throws MandatoryEventException    if the event is mandatory but have no listener observing it
     * @throws NotPopulatedEventException if the event is supposed to be populated with data but it isn't
     */
    public function dispatch($eventName, $shortEventClassName, array $eventArgs = [])
    {
        //@todo CoreBundle should be removed from that code
        $className = class_exists($shortEventClassName) ?
            $shortEventClassName :
            "Claroline\CoreBundle\Event\\{$shortEventClassName}Event";

        if (!class_exists($className)) {
            throw new MissingEventClassException(
                "No event class matches the short name '{$shortEventClassName}' (looked for '{$className})"
            );
        }

        $rEvent = new \ReflectionClass($className);
        $event = $rEvent->newInstanceArgs($eventArgs);

        if ($event instanceof MandatoryEventInterface && !$this->eventDispatcher->hasListeners($eventName)) {
            throw new MandatoryEventException("No listener is attached to the '{$eventName}' event");
        }

        $this->eventDispatcher->dispatch($eventName, $event);

        if ($event instanceof DataConveyorEventInterface && !$event->isPopulated()) {
            throw new NotPopulatedEventException("Event object for '{$eventName}' was not populated as expected");
        }

        return $event;
    }

    public function hasListeners($eventName)
    {
        return $this->eventDispatcher->hasListeners($eventName);
    }
}
