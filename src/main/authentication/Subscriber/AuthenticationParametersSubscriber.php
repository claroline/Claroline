<?php

namespace Claroline\AuthenticationBundle\Subscriber;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AuthenticationBundle\Manager\AuthenticationManager;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AuthenticationParametersSubscriber implements EventSubscriberInterface
{
    private PlatformConfigurationHandler $config;
    private SerializerProvider $serializer;
    private AuthenticationManager $authenticationManager;

    public function __construct(
        PlatformConfigurationHandler $config,
        SerializerProvider $serializer,
        AuthenticationManager $authenticationManager
    ) {
        $this->config = $config;
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
        if ($authenticationParameters->getMinLength() === null) {

            $authenticationParameters->setMinLength(0);
            $authenticationParameters->setRequireLowercase(false);
            $authenticationParameters->setRequireUppercase(false);
            $authenticationParameters->setRequireSpecialChar(false);
            $authenticationParameters->setRequireNumber(false);

            $authenticationParameters->setHelpMessage('Default help message');
            $authenticationParameters->setChangePassword(true);
            $authenticationParameters->setInternalAccount(true);
            $authenticationParameters->setShowClientIp(false);
            $authenticationParameters->setRedirectAfterLoginOption('default_option');
            $authenticationParameters->setRedirectAfterLoginUrl(null);

            $this->authenticationManager->updateParameters($authenticationParameters);
        }

        $serializedParameters = $this->serializer->serialize($authenticationParameters);
        $event->setResponse($serializedParameters);
    }
}
