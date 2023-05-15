<?php

namespace Claroline\PrivacyBundle\Subscriber\Administration;

use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Event\CatalogEvents\ToolEvents;
use Claroline\CoreBundle\Event\Tool\OpenToolEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PrivacySubscriber implements EventSubscriberInterface
{
    const NAME = 'privacy';

    private ParametersSerializer $serializer;

    public function __construct(
    ParametersSerializer $serializer,
    ) {
        $this->serializer = $serializer;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ToolEvents::getEventName(ToolEvents::OPEN, Tool::ADMINISTRATION, static::NAME) => 'onOpen',
        ];
    }

    public function onOpen(OpenToolEvent $event): void
    {
        $parameters = $this->serializer->serialize();

        $event->setData([
            'lockedParameters' => $parameters['lockedParameters'] ?? [],
            'parameters' => $parameters,
        ]);
    }
}
