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

/**
 * @DI\Service
 */
class CookieLifetimeSetter
{
    private $ch;

    /**
     * @DI\InjectParams({
     *     "ch" = @DI\Inject("claroline.config.platform_config_handler")
     * })
     */
    public function __construct(PlatformConfigurationHandler $ch)
    {
        $this->ch = $ch;
    }

    /**
     * @DI\Observe("kernel.request")
     *
     * Sets the platform language.
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest($event)
    {
        $lifetime = $this->ch->getParameter('cookie_lifetime');
        //A proper solution would need to use NativeSessionStorage but I don't know how to implement it.
        //An other one would be to edit the framework.yml files directly.
        session_set_cookie_params($lifetime);
    }
}
