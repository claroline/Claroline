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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * @DI\Service
 */
class MaintenanceListener
{
    /**
     * The list of public routes of the application.
     * NB. This is not the best place to declare it.
     *
     * @var array
     */
    const PUBLIC_ROUTES = [
        'claro_index',

        // allow login to let administrator access the platform.
        'claro_security_login',
        'claro_security_login_check',
    ];

    /**
     * PlatformListener constructor.
     *
     * @DI\InjectParams({
     *     "tokenStorage"  = @DI\Inject("security.token_storage"),
     *     "router"        = @DI\Inject("router"),
     *     "config"        = @DI\Inject("claroline.config.platform_config_handler"),
     *     "requestStack"  = @DI\Inject("request_stack")
     * })
     *
     * @param TokenStorageInterface        $tokenStorage
     * @param PlatformConfigurationHandler $config
     * @param RequestStack                 $requestStack
     * @param Router                       $router
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        Router $router,
        PlatformConfigurationHandler $config,
        RequestStack $requestStack
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->config = $config;
        $this->requestStack = $requestStack;
        $this->router = $router;
    }

    /**
     * @DI\Observe("kernel.request")
     *
     * @param GetResponseEvent $event
     */
    public function redirect(GetResponseEvent $event)
    {
        $isAdmin = false;
        $connected = false;

        if ($token = $this->tokenStorage->getToken()) {
            foreach ($this->tokenStorage->getToken()->getRoles() as $role) {
                if ('ROLE_ADMIN' === $role->getRole()) {
                    $isAdmin = true;
                }
            }

            if ($token instanceof UsernamePasswordToken) {
                $connected = true;
            }
        }
        $currentUri = $this->requestStack->getMasterRequest()->getUri();
        $url = $this->router->generate('claroline_maintenance_alert');

        if (!$isAdmin && $connected && $this->config->getParameter('maintenance.enable') && !strpos($currentUri, $url)) {
            $response = new RedirectResponse($url);

            $event->setResponse($response);
        }
    }
}
