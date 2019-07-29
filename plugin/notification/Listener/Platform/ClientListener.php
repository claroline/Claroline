<?php

namespace Icap\NotificationBundle\Listener\Platform;

use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Event\Layout\InjectStylesheetEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;

/**
 * @DI\Service
 */
class ClientListener
{
    /** @var TwigEngine */
    private $templating;

    /** @var PlatformConfigurationHandler */
    private $configHandler;

    /**
     * ClientListener constructor.
     *
     * @DI\InjectParams({
     *     "templating"    = @DI\Inject("templating"),
     *     "configHandler" = @DI\Inject("claroline.config.platform_config_handler")
     * })
     *
     * @param TwigEngine                   $templating
     * @param PlatformConfigurationHandler $configHandler
     */
    public function __construct(
        TwigEngine $templating,
        PlatformConfigurationHandler $configHandler
    ) {
        $this->templating = $templating;
        $this->configHandler = $configHandler;
    }

    /**
     * Appends notifications configuration to the global config object.
     *
     * @DI\Observe("claroline_populate_client_config")
     *
     * @param GenericDataEvent $event
     */
    public function onConfig(GenericDataEvent $event)
    {
        $event->setData([
            'notifications' => [
                'enabled' => $this->configHandler->getParameter('is_notification_active'),
                'refreshDelay' => $this->configHandler->getParameter('notifications_refresh_delay'),
            ],
        ]);
    }

    /**
     * @DI\Observe("layout.inject.stylesheet")
     *
     * @param InjectStylesheetEvent $event
     */
    public function onInjectCss(InjectStylesheetEvent $event)
    {
        $content = $this->templating->render('IcapNotificationBundle:layout:stylesheets.html.twig', []);

        $event->addContent($content);
    }
}
