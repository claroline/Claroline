<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener\Tool;

use Claroline\CoreBundle\Event\DisplayToolEvent;
use Claroline\CoreBundle\Manager\HomeTabManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @DI\Service
 */
class HomeListener
{
    private $container;
    private $httpKernel;
    private $homeTabManager;
    private $templating;
    private $workspaceManager;

    /**
     * @DI\InjectParams({
     *     "container"          = @DI\Inject("service_container"),
     *     "httpKernel"         = @DI\Inject("http_kernel"),
     *     "homeTabManager"     = @DI\Inject("claroline.manager.home_tab_manager"),
     *     "templating"         = @DI\Inject("templating"),
     *     "workspaceManager"   = @DI\Inject("claroline.manager.workspace_manager")
     * })
     */
    public function __construct(
        ContainerInterface $container,
        HttpKernelInterface $httpKernel,
        HomeTabManager $homeTabManager,
        TwigEngine $templating,
        WorkspaceManager $workspaceManager
    ) {
        $this->container = $container;
        $this->httpKernel = $httpKernel;
        $this->homeTabManager = $homeTabManager;
        $this->templating = $templating;
        $this->workspaceManager = $workspaceManager;
    }

    /**
     * @DI\Observe("open_tool_desktop_home")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayDesktopHome(DisplayToolEvent $event)
    {
        $params = ['_controller' => 'ClarolineCoreBundle:Tool\Home:desktopHomeDisplay'];
        $subRequest = $this->container->get('request')->duplicate([], null, $params);
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

        $event->setContent($response->getContent());
    }

    /**
     * @DI\Observe("open_tool_workspace_home")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayWorkspaceHome(DisplayToolEvent $event)
    {
        $params = [
            '_controller' => 'ClarolineCoreBundle:Tool\Home:workspaceHomeDisplay',
            'workspace' => $event->getWorkspace()->getId(),
        ];
        $subRequest = $this->container->get('request')->duplicate([], null, $params);
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

        $event->setContent($response->getContent());
    }
}
