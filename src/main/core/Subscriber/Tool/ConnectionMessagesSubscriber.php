<?php

namespace Claroline\CoreBundle\Subscriber\Tool;

use Claroline\CoreBundle\Entity\Tool\AbstractTool;
use Claroline\CoreBundle\Event\CatalogEvents\ToolEvents;
use Claroline\CoreBundle\Event\Tool\OpenToolEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ConnectionMessagesSubscriber implements EventSubscriberInterface
{
    const NAME = 'connection_messages';

    public static function getSubscribedEvents(): array
    {
        return [
            ToolEvents::getEventName(ToolEvents::OPEN, AbstractTool::ADMINISTRATION, static::NAME) => 'onOpen',
        ];
    }

    public function onOpen(OpenToolEvent $event): void
    {
        $event->setData([
            // You can put here the serialized data which need be loaded when the tool is opened
        ]);
    }
}
