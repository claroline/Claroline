<?php

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Event\CatalogEvents\FacetEvents;
use Claroline\CoreBundle\Event\Facet\GetFacetValueEvent;
use Claroline\CoreBundle\Event\Facet\SetFacetValueEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class FacetManager
{
    /** @var EventDispatcherInterface */
    private $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function serializeFieldValue($object, $type, $value = null)
    {
        $event = new GetFacetValueEvent(
            $object,
            $type,
            $value
        );

        $this->dispatcher->dispatch($event, FacetEvents::getEventName(FacetEvents::GET_VALUE, $type));

        return $event->getFormattedValue();
    }

    public function deserializeFieldValue($object, $type, $value = null)
    {
        $event = new SetFacetValueEvent(
            $object,
            $type,
            $value
        );

        $this->dispatcher->dispatch($event, FacetEvents::getEventName(FacetEvents::SET_VALUE, $type));

        return $event->getFormattedValue();
    }
}
