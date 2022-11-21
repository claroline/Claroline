<?php

namespace Claroline\ThemeBundle\Subscriber;

use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\ThemeBundle\Manager\IconSetManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlatformSubscriber implements EventSubscriberInterface
{
    /** @var PlatformConfigurationHandler */
    private $config;
    /** @var IconSetManager */
    private $iconManager;

    public function __construct(
        PlatformConfigurationHandler $config,
        IconSetManager $iconManager
    ) {
        $this->config = $config;
        $this->iconManager = $iconManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'claroline_populate_client_config' => 'onConfig',
        ];
    }

    public function onConfig(GenericDataEvent $event)
    {
        $event->setResponse([
            'theme' => [
                'name' => strtolower($this->config->getParameter('theme')),
                'icons' => $this->iconManager->getCurrentSet(),
            ],
        ]);
    }
}
