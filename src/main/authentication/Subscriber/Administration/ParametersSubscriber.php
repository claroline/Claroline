<?php

namespace Claroline\AuthenticationBundle\Subscriber\Administration;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\AuthenticationBundle\Entity\AuthenticationParameters;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Event\CatalogEvents\ToolEvents;
use Claroline\CoreBundle\Event\Tool\OpenToolEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ParametersSubscriber implements EventSubscriberInterface
{
    const NAME = 'main_settings';
    private SerializerProvider $serializer;
    private ObjectManager $objectManager;

    public function __construct(
        SerializerProvider $serializer,
        ObjectManager $objectManager
    ) {
        $this->serializer = $serializer;
        $this->objectManager = $objectManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ToolEvents::getEventName(ToolEvents::OPEN, Tool::ADMINISTRATION, static::NAME) => 'onOpen',
        ];
    }

    public function onOpen(OpenToolEvent $event): void
    {
        $event->setData([
            'authentication' => $this->serializer->serialize(
                $this->objectManager->getRepository(AuthenticationParameters::class)->findOneBy([]),
            ),
        ]);
    }
}
