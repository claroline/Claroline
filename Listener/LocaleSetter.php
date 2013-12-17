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
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Observe;
use JMS\DiExtraBundle\Annotation\Service;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * @Service
 *
 * Listener setting the platform language according to platform_options.yml.
 */
class LocaleSetter
{
    private $defaultLocale;
    private $context;

    /**
     * @InjectParams({
     *     "configHandler" = @Inject("claroline.config.platform_config_handler"),
     *     "context"       = @Inject("security.context")
     * })
     *
     * Constructor.
     *
     * @param PlatformConfigurationHandler $configHandler
     */
    public function __construct(PlatformConfigurationHandler $configHandler, SecurityContext $context)
    {
        $this->defaultLocale = $configHandler->getParameter('locale_language');
        $this->context = $context;
    }

    /**
     * @Observe("kernel.request")
     *
     * Sets the platform language.
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $user = $this->context->getToken()->getUser();

        if (!$request->hasPreviousSession()) {
            return;
        }

        // try to see if the locale has been set in locale user setting
        if (is_object($user) and $user->getLocale() and $user->getLocale() !== '') {
            $request->getSession()->set('_locale', $user->getLocale());
        }

        // try to see if the locale has been set as a _locale routing parameter
        if ($locale = $request->attributes->get('_locale')) {
            $request->getSession()->set('_locale', $locale);
        } else {
            // if no explicit locale has been set on this request, use one from the session
            $request->setLocale($request->getSession()->get('_locale', $this->defaultLocale));
        }
    }
}
