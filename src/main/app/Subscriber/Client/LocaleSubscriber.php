<?php

namespace Claroline\AppBundle\Subscriber;

use Claroline\AppBundle\Event\Client\UserPreferencesEvent;
use Claroline\AppBundle\Event\ClientEvents;
use Claroline\CoreBundle\Manager\LocaleManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Exposes user favorites to the web client.
 */
class LocaleSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly LocaleManager $localeManager
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ClientEvents::USER_PREFERENCES => 'onUserPreferences',
        ];
    }

    public function onUserPreferences(UserPreferencesEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();

        $event->addPreferences('locale', $this->localeManager->getUserLocale($request));
    }
}
