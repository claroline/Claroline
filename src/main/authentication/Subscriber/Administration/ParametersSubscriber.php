<?php

namespace Claroline\AuthenticationBundle\Subscriber\Administration;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AuthenticationBundle\Manager\AuthenticationManager;
use Claroline\AuthenticationBundle\Entity\AuthenticationParameters;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Event\CatalogEvents\ToolEvents;
use Claroline\CoreBundle\Event\Tool\OpenToolEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ParametersSubscriber implements EventSubscriberInterface
{
    const NAME = 'main_settings';
    private SerializerProvider $serializer;
    private AuthenticationManager $authenticationManager;

    public function __construct(
        SerializerProvider $serializer,
        AuthenticationManager $authenticationManager
    ) {
        $this->serializer = $serializer;
        $this->authenticationManager = $authenticationManager;
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
                $this->authenticationManager->getParameters()
            ),
        ]);
    }
}
