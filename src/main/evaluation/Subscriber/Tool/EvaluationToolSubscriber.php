<?php

namespace Claroline\EvaluationBundle\Subscriber\Tool;

use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Event\CatalogEvents\ToolEvents;
use Claroline\CoreBundle\Event\Tool\OpenToolEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EvaluationToolSubscriber implements EventSubscriberInterface
{
    const NAME = 'evaluation';

    public static function getSubscribedEvents(): array
    {
        return [
            ToolEvents::getEventName(ToolEvents::OPEN, Tool::DESKTOP, static::NAME) => 'onOpen',
            ToolEvents::getEventName(ToolEvents::OPEN, Tool::WORKSPACE, static::NAME) => 'onOpen',
        ];
    }

    public function onOpen(OpenToolEvent $event)
    {
        $event->setData([]);
        $event->stopPropagation();
    }
}
