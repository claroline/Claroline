<?php

namespace Claroline\ThemeBundle\Subscriber;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Event\Client\ConfigureEvent;
use Claroline\AppBundle\Event\Client\UserPreferencesEvent;
use Claroline\AppBundle\Event\ClientEvents;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\ThemeBundle\Entity\ColorCollection;
use Claroline\ThemeBundle\Manager\ThemeManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlatformSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ObjectManager $om,
        private readonly SerializerProvider $serializer,
        private readonly ThemeManager $themeManager
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ClientEvents::CONFIGURE => 'onClientConfig',
            ClientEvents::USER_PREFERENCES => 'onUserPreferences',
        ];
    }

    public function onClientConfig(ConfigureEvent $event): void
    {
        $colorCharts = $this->om->getRepository(ColorCollection::class)->findAll();

        $event->setParameters([
            'colorChart' => array_map(function (ColorCollection $colorCollection) {
                return $this->serializer->serialize($colorCollection);
            }, $colorCharts),
        ]);
    }

    public function onUserPreferences(UserPreferencesEvent $event): void
    {
        $event->addPreferences('theme', $this->themeManager->getAppearance($event->getUser()));
    }
}
