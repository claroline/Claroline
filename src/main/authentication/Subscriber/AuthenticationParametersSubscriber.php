<?php

namespace Claroline\AuthenticationBundle\Subscriber;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Event\Client\ConfigureEvent;
use Claroline\AppBundle\Event\ClientEvents;
use Claroline\AuthenticationBundle\Manager\AuthenticationManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AuthenticationParametersSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly SerializerProvider $serializer,
        private readonly AuthenticationManager $authenticationManager
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
        $authenticationParameters = $this->authenticationManager->getParameters();
        $serializedParameters = $this->serializer->serialize($authenticationParameters);

        $event->setParameters([
            'authentication' => $serializedParameters,
        ]);
    }
}
