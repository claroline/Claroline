<?php

namespace Claroline\CoreBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;

/**
 * Listener setting the platform language according to platform_options.yml.
 */
class LocaleSetter
{
    protected $configHandler;

    /**
     * Constructor.
     *
     * @param PlatformConfigurationHandler $configHandler
     */
    public function __construct(PlatformConfigurationHandler $configHandler)
    {
        $this->configHandler = $configHandler;
    }

    /**
     * Sets the platform language.
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $event->getRequest()->setLocale($this->configHandler->getParameter('locale_language'));
    }
}