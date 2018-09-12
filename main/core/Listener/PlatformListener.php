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
use Claroline\CoreBundle\Manager\LocaleManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PlatformListener
{
    /** @var Kernel */
    private $kernel;

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
        'claro_index',

        // why do we allow to log to an unavailable platform ?
        'claro_security_login',
        'claro_security_login_check',
    ];

    /**
     * PlatformListener constructor.
     *
     * @DI\InjectParams({
     *     "kernel"        = @DI\Inject("kernel"),
     *     "tokenStorage"  = @DI\Inject("security.token_storage"),
     *     "config"        = @DI\Inject("claroline.config.platform_config_handler"),
     *     "tempManager"   = @DI\Inject("claroline.manager.temp_file"),
     *     "localeManager" = @DI\Inject("claroline.manager.locale_manager")
     * })
     *
     * @param Kernel                       $kernel
     * @param TokenStorageInterface        $tokenStorage
     * @param PlatformConfigurationHandler $config
     * @param TempFileManager              $tempManager
     * @param LocaleManager                $localeManager
     */
    public function __construct(
        Kernel $kernel,
        TokenStorageInterface $tokenStorage,
        PlatformConfigurationHandler $config,
        TempFileManager $tempManager,
        LocaleManager $localeManager)
    {
        $this->kernel = $kernel;
        $this->tokenStorage = $tokenStorage;
        $this->config = $config;
        $this->tempManager = $tempManager;
        $this->localeManager = $localeManager;
    }

    /**
     * Sets the platform language.
     *
     * @DI\Observe("kernel.request", priority = 17)
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
     *   - Checks are enabled only on productions.
     *   - Administrators are always granted.
     *   - Public routes are still accessible.
     *
     * @DI\Observe("kernel.request")
     *
     * @param GetResponseEvent $event
     */
    public function checkAvailability(GetResponseEvent $event)
    {
        if ('prod' === $this->kernel->getEnvironment() && $event->isMasterRequest()) {
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

            $now = time();
            $minDate = $this->config->getParameter('platform_init_date');
            $expirationDate = $this->config->getParameter('platform_limit_date');

            if (!$isAdmin &&
                !in_array($event->getRequest()->get('_route'), static::PUBLIC_ROUTES) &&
                ($minDate > $now || $now > $expirationDate)
            ) {
                throw new HttpException(503, 'Platform is not available.');
            }
        }
    }

    /**
     * Clears all temp files at the end of each request.
     *
     * @DI\Observe("kernel.terminate")
     */
    public function clearTemp()
    {
        $this->tempManager->clear();
    }
}
