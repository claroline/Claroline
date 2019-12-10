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

use Claroline\AppBundle\Manager\File\TempFileManager;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Manager\LocaleManager;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PlatformListener
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var PlatformConfigurationHandler */
    private $config;

    /** @var TempFileManager */
    private $tempManager;

    /** @var LocaleManager */
    private $localeManager;

    /**
     * The list of public routes of the application.
     * NB. This is not the best place to declare it.
     *
     * @var array
     */
    const PUBLIC_ROUTES = [
        // to let admin log in
        'claro_security_login',
        // to be able to render client UI
        'claro_index',
        'fos_js_routing_js',
        // to have access to debug tools
        '_wdt',
        '_profiler',
    ];

    /**
     * PlatformListener constructor.
     *
     * @param TokenStorageInterface        $tokenStorage
     * @param PlatformConfigurationHandler $config
     * @param TempFileManager              $tempManager
     * @param LocaleManager                $localeManager
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        PlatformConfigurationHandler $config,
        TempFileManager $tempManager,
        LocaleManager $localeManager)
    {
        $this->tokenStorage = $tokenStorage;
        $this->config = $config;
        $this->tempManager = $tempManager;
        $this->localeManager = $localeManager;
    }

    /**
     * Sets the platform language.
     *
     * @param GetResponseEvent $event
     */
    public function setLocale(GetResponseEvent $event)
    {
        if ($event->isMasterRequest()) {
            $request = $event->getRequest();

            $locale = $this->localeManager->getUserLocale($request);
            $request->setLocale($locale);
        }
    }

    /**
     * Checks the app availability before displaying the platform.
     *
     * @param GetResponseEvent $event
     */
    public function checkAvailability(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest() || in_array($event->getRequest()->get('_route'), static::PUBLIC_ROUTES)) {
            return;
        }

        $disabled = false;

        // checks platform restrictions
        if ($this->config->getParameter('restrictions.disabled')) {
            $disabled = true;
        }

        $dates = $this->config->getParameter('restrictions.dates');
        if (!empty($dates)) {
            $now = new \DateTime();
            if (!empty($dates[0]) && DateNormalizer::normalize($now) < $dates[0]) {
                $disabled = true;
            }

            if (!empty($dates[1]) && DateNormalizer::normalize($now) > $dates[1]) {
                $disabled = true;
            }
        }

        // checks platform maintenance
        if ($this->config->getParameter('maintenance.enable')) {
            // only disable for non admin
            $isAdmin = false;
            $token = $this->tokenStorage->getToken();
            if ($token) {
                foreach ($token->getRoles() as $role) {
                    if ('ROLE_ADMIN' === $role->getRole()) {
                        $isAdmin = true;
                        break;
                    }
                }
            }

            $disabled = !$isAdmin;
        }

        if ($disabled) {
            throw new HttpException(503, 'Platform is not available.');
        }
    }

    /**
     * Clears all temp files at the end of each request.
     */
    public function clearTemp()
    {
        $this->tempManager->clear();
    }
}
