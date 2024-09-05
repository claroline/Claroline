<?php

namespace Claroline\AppBundle\Manager;

use Claroline\AppBundle\Event\Client\ConfigureEvent;
use Claroline\AppBundle\Event\Client\InjectJavascriptEvent;
use Claroline\AppBundle\Event\Client\InjectStylesheetEvent;
use Claroline\AppBundle\Event\Client\UserPreferencesEvent;
use Claroline\AppBundle\Event\ClientEvents;
use Claroline\CoreBundle\API\Serializer\Platform\ClientSerializer;
use Claroline\CoreBundle\Entity\User;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ClientManager
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly PlatformManager $platformManager,
        private readonly ClientSerializer $clientSerializer
    ) {
    }

    public function getBaseUrl(): string
    {
        return $this->platformManager->getUrl();
    }

    /**
     * Load user related data.
     */
    public function getUserPreferences(?User $user = null): array
    {
        $event = new UserPreferencesEvent($user);
        $this->eventDispatcher->dispatch($event, ClientEvents::USER_PREFERENCES);

        return $event->getPreferences();
    }

    public function getParameters(): array
    {
        $baseParameters = $this->clientSerializer->serialize();

        // load additional configuration from plugins
        $event = new ConfigureEvent($baseParameters);
        $this->eventDispatcher->dispatch($event, ClientEvents::CONFIGURE);

        return $event->getParameters();
    }

    /**
     * Gets the javascript injected by the plugins if any.
     */
    public function getJavascripts(): string
    {
        $event = new InjectJavascriptEvent();
        $this->eventDispatcher->dispatch($event, ClientEvents::JAVASCRIPTS);

        return $event->getContent();
    }

    /**
     * Gets the styles injected by the plugins if any.
     */
    public function getStylesheets(): string
    {
        $event = new InjectStylesheetEvent();
        $this->eventDispatcher->dispatch($event, ClientEvents::STYLESHEETS);

        return $event->getContent();
    }
}
