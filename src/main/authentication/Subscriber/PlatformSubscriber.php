<?php

namespace Claroline\AuthenticationBundle\Subscriber;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Event\Client\ConfigureEvent;
use Claroline\AppBundle\Event\ClientEvents;
use Claroline\AuthenticationBundle\Manager\AuthenticationManager;
use Claroline\AuthenticationBundle\Manager\OAuthManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Expose authentication parameters to the platform client.
 */
class PlatformSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly SerializerProvider $serializer,
        private readonly AuthenticationManager $authenticationManager,
        private readonly OAuthManager $oauthManager,
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

        $oauthButtons = $this->oauthManager->getDisplayedButtons();

        $event->setParameters([
            'authentication' => array_merge($serializedParameters, [
                'sso' => $oauthButtons,
            ]),
        ]);
    }
}
