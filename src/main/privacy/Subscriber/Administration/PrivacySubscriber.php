<?php

use Claroline\CoreBundle\Entity\Tool\AbstractTool;
use Claroline\CoreBundle\Event\CatalogEvents\ToolEvents;
use Claroline\CoreBundle\Event\Tool\OpenToolEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PrivacySubscriber implements EventSubscriberInterface
{
    const NAME = 'privacy';

    public static function getSubscribedEvents(): array
    {
        return [
            ToolEvents::getEventName(ToolEvents::OPEN, AbstractTool::ADMINISTRATION, static::NAME) => 'onOpen',
        ];
    }

    public function onOpen(OpenToolEvent $event): void
    {
        $event->setData([
            'dpo' => [/* serializable structure */],
        ]);
    }
}