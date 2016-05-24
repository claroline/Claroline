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

use Claroline\CoreBundle\Manager\LocaleManager;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Observe;
use JMS\DiExtraBundle\Annotation\Service;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * @Service("claroline.locale_setter")
 * Listener setting the platform language according to platform_options.yml.
 */
class LocaleSetter
{
    private $localeManager;

    /**
     * @InjectParams({
     *     "localeManager"  = @Inject("claroline.manager.locale_manager")
     * })
     *
     * @param LocaleManager $localeManager
     */
    public function __construct(LocaleManager $localeManager)
    {
        $this->localeManager = $localeManager;
    }

    /**
     * @Observe("kernel.request", priority = 17)
     *
     * Sets the platform language.
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $locale = $this->localeManager->getUserLocale($request);
        $request->setLocale($locale);
    }
}
