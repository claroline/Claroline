<?php

namespace Claroline\PrivacyBundle\Subscriber;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Event\Client\ConfigureEvent;
use Claroline\AppBundle\Event\ClientEvents;
use Claroline\PrivacyBundle\Manager\PrivacyManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ClientSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly SerializerProvider $serializer,
        private readonly PrivacyManager $privacyManager
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ClientEvents::CONFIGURE => 'onClientConfig',
        ];
    }

    public function onClientConfig(ConfigureEvent $event): void
    {
        $privacyParameters = $this->privacyManager->getParameters();
        $serializedParameters = $this->serializer->serialize($privacyParameters);

        $event->setParameters([
            'privacy' => $serializedParameters,
        ]);
    }
}
