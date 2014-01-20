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
use Gedmo\Translatable\TranslatableListener;
use Stof\DoctrineExtensionsBundle\EventListener\LocaleListener;

/**
 * @Service
 *
 * Listener setting the platform language according to platform_options.yml.
 */
class LocaleSetter
{
    private $localeManager;
   // private $translatableListener;
    //     "translatableListener" = @Inject("stof_doctrine_extensions.listener.translatable")

    /**
     * @InjectParams({
     *     "localeManager"  = @Inject("claroline.common.locale_manager")
     * })
     */
    public function __construct(LocaleManager $localeManager/*, TranslatableListener $translatableListener*/)
    {
        $this->localeManager = $localeManager;
//        $this->translatableListener = $translatableListener;
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
        $request->setLocale($this->localeManager->getUserLocale($request));
        //$this->translatableListener->setDefaultLocale("en");
    }
}