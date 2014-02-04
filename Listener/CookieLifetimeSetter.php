<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * @DI\Service
 */
class CookieLifetimeSetter
{
    private $configurationHandler;

    /**
     * @DI\InjectParams({
     *     "configurationHandler" = @DI\Inject("claroline.config.platform_config_handler")
     * })
     */
    public function __construct(PlatformConfigurationHandler $configurationHandler)
    {
        $this->configurationHandler = $configurationHandler;
    }

    /**
     * @DI\Observe("kernel.request")
     *
     * Sets the platform session lifetime.
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
//        $lifetime = $this->configurationHandler->getParameter('cookie_lifetime');
//        $request = $event->getRequest();
//        $session = $request->getSession();
//        $session->migrate(false, $lifetime);
    }
}
