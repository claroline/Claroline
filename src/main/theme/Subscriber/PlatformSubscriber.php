<?php

namespace Claroline\ThemeBundle\Subscriber;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\ThemeBundle\Entity\ColorCollection;
use Claroline\ThemeBundle\Manager\IconSetManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlatformSubscriber implements EventSubscriberInterface
{
    private PlatformConfigurationHandler $config;
    private IconSetManager $iconManager;
    private ObjectManager $objectManager;
    private SerializerProvider $serializer;

    public function __construct(
        PlatformConfigurationHandler $config,
        IconSetManager $iconManager,
        ObjectManager $objectManager,
        SerializerProvider $serializer
    ) {
        $this->config = $config;
        $this->iconManager = $iconManager;
        $this->objectManager = $objectManager;
        $this->serializer = $serializer;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'claroline_populate_client_config' => 'onConfig',
        ];
    }

    public function onConfig(GenericDataEvent $event): void
    {
        $colorCharts = $this->objectManager->getRepository(ColorCollection::class)->findAll();
        $chartsData = [];

        foreach ($colorCharts as $chart) {
            $chartsData[] = $this->serializer->serialize($chart);
        }

        $event->setResponse([
            'theme' => [
                'name' => strtolower($this->config->getParameter('theme')),
                'icons' => $this->iconManager->getCurrentSet(),
            ],
            'colorChart' => $chartsData,
        ]);
    }
}
