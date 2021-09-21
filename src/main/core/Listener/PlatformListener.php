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
use Claroline\CoreBundle\Library\Maintenance\MaintenanceHandler;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Manager\LocaleManager;
use Symfony\Component\HttpKernel\Event\RequestEvent;
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

        // open reactivation routes
        'apiv2_platform_extend',
        'apiv2_platform_enable',
    ];

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
     */
    public function setLocale(RequestEvent $event)
    {
        if ($event->isMasterRequest()) {
            $request = $event->getRequest();

            $locale = $this->localeManager->getUserLocale($request);
            $request->setLocale($locale);
        }
    }

    /**
     * Checks the app availability before displaying the platform.
     */
    public function checkAvailability(RequestEvent $event)
    {
        if (!$event->isMasterRequest() || in_array($event->getRequest()->get('_route'), static::PUBLIC_ROUTES)) {
            return;
        }

        // checks platform restrictions
        if ($this->config->getParameter('restrictions.disabled')) {
            throw new HttpException(503, 'Platform is not available (Platform is disabled).');
        }

        $dates = $this->config->getParameter('restrictions.dates');
        if (!empty($dates)) {
            $now = new \DateTime();
            if (!empty($dates[0]) && DateNormalizer::normalize($now) < $dates[0]) {
                throw new HttpException(503, 'Platform is not available (Platform start date not reached).');
            }

            if (!empty($dates[1]) && DateNormalizer::normalize($now) > $dates[1]) {
                throw new HttpException(503, 'Platform is not available (Platform end date reached).');
            }
        }

        // checks platform maintenance
        if (MaintenanceHandler::isMaintenanceEnabled() || $this->config->getParameter('maintenance.enable')) {
            // only disable for non admin
            // TODO : it may break the impersonation mode
            $isAdmin = false;
            $token = $this->tokenStorage->getToken();
            if ($token) {
                foreach ($token->getRoleNames() as $role) {
                    if ('ROLE_ADMIN' === $role) {
                        $isAdmin = true;
                        break;
                    }
                }
            }

            if (!$isAdmin) {
                throw new HttpException(503, 'Platform is not available (Platform is under maintenance).');
            }
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
