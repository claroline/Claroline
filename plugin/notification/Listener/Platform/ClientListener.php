<?php

namespace Icap\NotificationBundle\Listener\Platform;

use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Event\Layout\InjectStylesheetEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Twig\Environment;

class ClientListener
{
    /** @var Environment */
    private $templating;

    /** @var PlatformConfigurationHandler */
    private $configHandler;

    /**
     * ClientListener constructor.
     */
    public function __construct(
        Environment $templating,
        PlatformConfigurationHandler $configHandler
    ) {
        $this->templating = $templating;
        $this->configHandler = $configHandler;
    }

    /**
     * Appends notifications configuration to the global config object.
     */
    public function onConfig(GenericDataEvent $event)
    {
        $event->setResponse([
            'notifications' => [
                'enabled' => $this->configHandler->getParameter('is_notification_active'),
                'refreshDelay' => $this->configHandler->getParameter('notifications_refresh_delay'),
            ],
        ]);
    }

    public function onInjectCss(InjectStylesheetEvent $event)
    {
        $content = $this->templating->render('@IcapNotification/layout/stylesheets.html.twig', []);

        $event->addContent($content);
    }
}
