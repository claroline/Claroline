<?php

namespace Claroline\LogBundle\Subscriber;

use Claroline\CoreBundle\Event\Tool\OpenToolEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AdminLogToolSubscriber implements EventSubscriberInterface
{
    private const ADMINNISTRATION_TOOL_LOGS = 'administration_tool_logs';

    public static function getSubscribedEvents(): array
    {
        return [
            self::ADMINNISTRATION_TOOL_LOGS => 'onAdministrationToolOpen',
        ];
    }

    public function onAdministrationToolOpen(OpenToolEvent $event)
    {
        $event->setData([]);
        $event->stopPropagation();
    }
}
