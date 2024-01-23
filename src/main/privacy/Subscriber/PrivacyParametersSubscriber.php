<?php

namespace Claroline\PrivacyBundle\Subscriber;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\PrivacyBundle\Manager\PrivacyManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PrivacyParametersSubscriber implements EventSubscriberInterface
{
    private SerializerProvider $serializer;
    private PrivacyManager $privacyManager;

    public function __construct(
        SerializerProvider $serializer,
        PrivacyManager $privacyManager
    ) {
        $this->serializer = $serializer;
        $this->privacyManager = $privacyManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'claroline_populate_client_config' => 'onPopulateConfig',
        ];
    }

    public function onPopulateConfig(GenericDataEvent $event): void
    {
        $privacyParameters = $this->privacyManager->getParameters();
        $serializedParameters = $this->serializer->serialize($privacyParameters);
        $event->setResponse([
            'privacy' => $serializedParameters,
        ]);
    }
}
