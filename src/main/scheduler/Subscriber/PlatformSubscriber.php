<?php

namespace Claroline\SchedulerBundle\Subscriber;

use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlatformSubscriber implements EventSubscriberInterface
{
    /** @var PlatformConfigurationHandler */
    private $config;

    public function __construct(PlatformConfigurationHandler $config)
    {
        $this->config = $config;
    }

    public static function getSubscribedEvents()
    {
        return [
            'claroline_populate_client_config' => 'onClientConfig',
        ];
    }

    public function onClientConfig(GenericDataEvent $event)
    {
        // let the claroline UI know the scheduler is enabled.
        // this allows plugins which use the scheduler to show some ui for planing when the scheduler is here.
        // replace by a check on plugin status when the plugin activation/deactivation is ready.
        $event->setResponse([
            'schedulerEnabled' => $this->config->getParameter('is_cron_configured') ?? false,
        ]);
    }
}
