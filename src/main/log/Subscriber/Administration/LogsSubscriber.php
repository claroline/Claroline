<?php

namespace Claroline\LogBundle\Subscriber\Administration;

use Claroline\CoreBundle\Event\Tool\OpenToolEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LogsSubscriber implements EventSubscriberInterface
{
    private const ADMINISTRATION_TOOL_LOGS = 'administration_tool_logs';

    public static function getSubscribedEvents(): array
    {
        return [
            self::ADMINISTRATION_TOOL_LOGS => 'onAdministrationToolOpen',
        ];
    }

    public function onAdministrationToolOpen(OpenToolEvent $event)
    {
        $event->setData([]);
        $event->stopPropagation();
    }
}
