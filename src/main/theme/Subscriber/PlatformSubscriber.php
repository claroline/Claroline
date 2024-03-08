<?php

namespace Claroline\ThemeBundle\Subscriber;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Event\Security\UserLoginEvent;
use Claroline\ThemeBundle\Entity\ColorCollection;
use Claroline\ThemeBundle\Manager\ThemeManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlatformSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ObjectManager $objectManager,
        private readonly SerializerProvider $serializer,
        private readonly ThemeManager $themeManager
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'claroline_populate_client_config' => 'onConfig',
            SecurityEvents::USER_LOGIN => 'onLogin',
        ];
    }

    public function onConfig(GenericDataEvent $event): void
    {
        $colorCharts = $this->objectManager->getRepository(ColorCollection::class)->findAll();

        $event->setResponse([
            'theme' => $this->themeManager->getAppearance(),
            'colorChart' => array_map(function (ColorCollection $colorCollection) {
                return $this->serializer->serialize($colorCollection);
            }, $colorCharts),
        ]);
    }

    public function onLogin(UserLoginEvent $event): void
    {
        $event->addResponse([
            'config' => ['theme' => $this->themeManager->getAppearance()],
        ]);
    }
}
