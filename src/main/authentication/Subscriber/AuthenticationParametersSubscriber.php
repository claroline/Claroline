<?php

namespace Claroline\AuthenticationBundle\Subscriber;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AuthenticationBundle\Manager\AuthenticationManager;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AuthenticationParametersSubscriber implements EventSubscriberInterface
{
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
            'claroline_populate_client_config' => 'onPopulateConfig',
        ];
    }

    public function onPopulateConfig(GenericDataEvent $event): void
    {
        $authenticationParameters = $this->authenticationManager->getParameters();
        $serializedParameters = $this->serializer->serialize($authenticationParameters);
        $event->setResponse([
            'authentication' => $serializedParameters,
        ]);
    }
}
