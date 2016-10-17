<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\DashboardBundle\Listener;

use Claroline\AgendaBundle\Manager\AgendaManager;
use Claroline\CoreBundle\Event\DisplayToolEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 *  @DI\Service()
 */
class DashboardListener
{
    private $templating;
    private $tokenStorage;
    private $authorization;
    private $container;
    private $router;
    private $request;
    private $httpKernel;
    private $agendaManager;

    /**
     * @DI\InjectParams({
     *     "templating"     = @DI\Inject("templating"),
     *     "authorization"  = @DI\Inject("security.authorization_checker"),
     *     "tokenStorage"   = @DI\Inject("security.token_storage"),
     *     "container"      = @DI\Inject("service_container"),
     *     "router"         = @DI\Inject("router"),
     *     "requestStack"   = @DI\Inject("request_stack"),
     *     "httpKernel"     = @DI\Inject("http_kernel"),
     *     "agendaManager"  = @DI\Inject("claroline.manager.agenda_manager")
     * })
     */
    public function __construct(
        TwigEngine $templating,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorization,
        ContainerInterface $container,
        RouterInterface $router,
        RequestStack $requestStack,
        HttpKernelInterface $httpKernel,
        AgendaManager $agendaManager
    ) {
        $this->templating = $templating;
        $this->tokenStorage = $tokenStorage;
        $this->authorization = $authorization;
        $this->container = $container;
        $this->router = $router;
        $this->request = $requestStack->getCurrentRequest();
        $this->httpKernel = $httpKernel;
        $this->agendaManager = $agendaManager;
    }

    /**
     * @DI\Observe("open_tool_desktop_dashboard")
     *
     * @param DisplayToolEvent $event
     */
    public function onDesktopOpen(DisplayToolEvent $event)
    {
        $content = $this->templating->render(
            'ClarolineDashboardBundle:Tool:dashboards.html.twig',
            [
                'title' => 'youpi',
            ]
        );
        $event->setContent($content);
    }
}
