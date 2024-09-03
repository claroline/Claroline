<?php

namespace Claroline\SchedulerBundle\Subscriber;

use Claroline\AppBundle\Event\Client\ConfigureEvent;
use Claroline\AppBundle\Event\ClientEvents;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlatformSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly PlatformConfigurationHandler $config
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
        // let the claroline UI know the scheduler is enabled.
        // this allows plugins which use the scheduler to show some ui for planing when the scheduler is here.
        // replace by a check on plugin status when the plugin activation/deactivation is ready.
        $event->setParameters([
            'schedulerEnabled' => $this->config->getParameter('is_cron_configured') ?? false,
        ]);
    }
}
