<?php

namespace Claroline\AuthenticationBundle\Subscriber;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\AuthenticationBundle\Entity\AuthenticationParameters;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AuthenticationParametersSubscriber implements EventSubscriberInterface
{

    private SerializerProvider $serializer;
    private ObjectManager $objectManager;
    private PlatformConfigurationHandler $config;

    public function __construct(
        PlatformConfigurationHandler $config,
        SerializerProvider $serializer,
        ObjectManager $objectManager
    ) {
        $this->config = $config;
        $this->serializer = $serializer;
        $this->objectManager = $objectManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'claroline_populate_client_config' => 'onPopulateConfig',
        ];
    }

    public function onPopulateConfig(GenericDataEvent $event): void
    {
        $event->setResponse([
            'authentication_parameters' => [
                "password-validate" => $this->serializer->serialize(
                    $this->objectManager->getRepository(AuthenticationParameters::class)->findOneBy([]),
                ),
            ]
        ]);
    }
}