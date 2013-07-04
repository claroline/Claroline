<?php

namespace Claroline\CoreBundle\Event;

use Symfony\Component\EventDispatcher\EventDispatcher;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.event.event_dispatcher")
 */
class Dispatcher
{
    /** @var EventDispatcher */
    private $ed;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "ed" = @DI\Inject("event_dispatcher")
     * })
     */
    public function __construct(EventDispatcher $ed)
    {
        $this->ed = $ed;
    }

    public function dispatch($eventName, $className, array $args = array())
    {
        $className = '\\Claroline\\CoreBundle\\Event\\Event\\' . $className . 'Event';
        $rEvent = new \ReflectionClass($className);
        $event = $rEvent->newInstanceArgs($args);

        if ($event instanceof MandatoryEventInterface) {
            if (!$this->ed->hasListeners($eventName)) {
                throw new MandatoryEventException("No listener is attached to the '{$eventName}' event");
            }
        }

         $this->ed->dispatch($eventName, $event);

         if ($event instanceof DataConveyorEventInterface) {
             if (!$event->isPopulated()) {
                 throw new PopulateEventException("Event object for '{$eventName}' was not populated as expected");
             }
         }

        return $event;
    }
}
