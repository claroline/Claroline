<?php

namespace Claroline\CoreBundle\Subscriber;

use Claroline\CoreBundle\Event\CatalogEvents\FunctionalEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FunctonalEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            FunctionalEvents::RESOURCE_ENTERING => 'logEvent',
            FunctionalEvents::RESOURCE_EXITING => 'logEvent',
        ];
    }
}
