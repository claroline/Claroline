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
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @DI\Service
 */
class HomeListener
{
    /** @var HttpKernelInterface */
    private $httpKernel;
    /** @var Request */
    private $request;

    /**
     * HomeListener constructor.
     *
     * @DI\InjectParams({
     *     "httpKernel"   = @DI\Inject("http_kernel"),
     *     "requestStack" = @DI\Inject("request_stack")
     * })
     *
     * @param HttpKernelInterface $httpKernel
     * @param RequestStack        $requestStack
     */
    public function __construct(
        HttpKernelInterface $httpKernel,
        RequestStack $requestStack
    ) {
        $this->httpKernel = $httpKernel;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * Displays home on Desktop.
     *
     * @DI\Observe("open_tool_desktop_home")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayDesktop(DisplayToolEvent $event)
    {
        $subRequest = $this->request->duplicate([], null, [
            '_controller' => 'ClarolineCoreBundle:Tool\Home:displayDesktop',
        ]);

        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setContent($response->getContent());
    }

    /**
     * Displays home on Workspace.
     *
     * @DI\Observe("open_tool_workspace_home")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayWorkspace(DisplayToolEvent $event)
    {
        $subRequest = $this->request->duplicate([], null, [
            '_controller' => 'ClarolineCoreBundle:Tool\Home:displayWorkspace',
            'workspace' => $event->getWorkspace()->getId(),
        ]);

        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setContent($response->getContent());
    }
}
