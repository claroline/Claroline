<?php

namespace Claroline\SchedulerBundle\Subscriber\Administration;

use Claroline\CoreBundle\Event\Tool\OpenToolEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Scheduled tasks tool.
 */
class ScheduledTaskToolSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            'administration_tool_scheduled_tasks' => 'onDisplayTool',
        ];
    }

    /**
     * Displays scheduled tasks administration tool.
     */
    public function onDisplayTool(OpenToolEvent $event)
    {
        $event->setData([]);
        $event->stopPropagation();
    }
}
