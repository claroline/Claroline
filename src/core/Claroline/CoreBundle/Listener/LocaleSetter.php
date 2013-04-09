<?php

namespace Claroline\CoreBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;

/**
 * @DI\Service
 *
 * Listener setting the platform language according to platform_options.yml.
 */
class LocaleSetter
{
    protected $configHandler;

    /**
     * @DI\InjectParams({
     *     "configHandler" = @DI\Inject("claroline.config.platform_config_handler")
     * })
     *
     * Constructor.
     *
     * @param PlatformConfigurationHandler $configHandler
     */
    public function __construct(PlatformConfigurationHandler $configHandler)
    {
        $this->configHandler = $configHandler;
    }

    /**
     * @DI\Observe("kernel.request")
     *
     * Sets the platform language.
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $event->getRequest()->setLocale($this->configHandler->getParameter('locale_language'));
    }
}