<?php

namespace Claroline\AppBundle\Subscriber;

use Claroline\AppBundle\Component\Context\ContextProvider;
use Claroline\AppBundle\Event\Client\UserPreferencesEvent;
use Claroline\AppBundle\Event\ClientEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Exposes user favorites to the web client.
 */
class ContextFavoriteSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ContextProvider $contextProvider
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ClientEvents::USER_PREFERENCES => ['onUserPreferences', 99],
        ];
    }

    public function onUserPreferences(UserPreferencesEvent $event): void
    {
        $event->addPreferences('favorites', $this->contextProvider->getFavoriteContexts());
    }
}
