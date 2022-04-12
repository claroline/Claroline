<?php

namespace Claroline\SchedulerBundle\Subscriber\Administration;

use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Event\CatalogEvents\ToolEvents;
use Claroline\CoreBundle\Event\Tool\OpenToolEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Scheduled tasks tool.
 */
class ScheduledTaskToolSubscriber implements EventSubscriberInterface
{
    const NAME = 'scheduled_tasks';

    public static function getSubscribedEvents(): array
    {
        return [
            ToolEvents::getEventName(ToolEvents::OPEN, Tool::ADMINISTRATION, static::NAME) => 'onOpen',
        ];
    }

    /**
     * Displays scheduled tasks administration tool.
     */
    public function onOpen(OpenToolEvent $event)
    {
        $event->setData([]);
        $event->stopPropagation();
    }
}
