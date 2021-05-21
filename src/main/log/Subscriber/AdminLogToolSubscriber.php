<?php

namespace Claroline\LogBundle\Subscriber;

use Claroline\CoreBundle\Event\Tool\OpenToolEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AdminLogToolSubscriber implements EventSubscriberInterface
{
    private const ADMINNISTRATION_TOOL_CLAROLINE_LOG_ADMIN_TOOL = 'administration_tool_claroline_log_admin_tool';

    public static function getSubscribedEvents(): array
    {
        return [
            self::ADMINNISTRATION_TOOL_CLAROLINE_LOG_ADMIN_TOOL => 'onAdministrationToolOpen',
        ];
    }

    public function onAdministrationToolOpen(OpenToolEvent $event)
    {
        $event->setData([]);
        $event->stopPropagation();
    }
}
