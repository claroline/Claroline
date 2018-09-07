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
use Claroline\CoreBundle\Manager\ToolManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Templating\EngineInterface;

// TODO : do not redirect to a controller, directly renders the tool template

/**
 * @DI\Service()
 */
class ToolListener
{
    private $httpKernel;
    private $templating;
    private $request;
    private $toolManager;

    /**
     * ToolListener constructor.
     *
     * @DI\InjectParams({
     *     "httpKernel"       = @DI\Inject("http_kernel"),
     *     "templating"       = @DI\Inject("templating"),
     *     "requestStack"     = @DI\Inject("request_stack"),
     *     "toolManager"      = @DI\Inject("claroline.manager.tool_manager")
     * })
     *
     * @param HttpKernel      $httpKernel
     * @param EngineInterface $templating
     * @param RequestStack    $requestStack
     * @param ToolManager     $toolManager
     */
    public function __construct(
        HttpKernel $httpKernel,
        EngineInterface $templating,
        RequestStack $requestStack,
        ToolManager $toolManager
    ) {
        $this->httpKernel = $httpKernel;
        $this->templating = $templating;
        $this->request = $requestStack->getMasterRequest();
        $this->toolManager = $toolManager;
    }

    /**
     * @DI\Observe("open_tool_desktop_parameters")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayDesktopParameters(DisplayToolEvent $event)
    {
        $desktopTools = $this->toolManager->getToolByCriterias([
            'isConfigurableInDesktop' => true,
            'isDisplayableInDesktop' => true,
        ]);

        $tools = [];
        foreach ($desktopTools as $desktopTool) {
            $toolName = $desktopTool->getName();

            if ('home' !== $toolName && 'parameters' !== $toolName) {
                $tools[] = $desktopTool;
            }
        }

        if (count($tools) > 1) {
            $event->setContent(
                $this->templating->render(
                    'ClarolineCoreBundle:Tool\desktop\parameters:parameters.html.twig',
                    ['tools' => $tools]
                )
            );
        }

        //otherwise only parameters exists so we return the parameters page.
        $subRequest = $this->request->duplicate([], null, [
            '_controller' => 'ClarolineCoreBundle:Tool\DesktopParameters:desktopParametersMenu',
        ]);
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

        $event->setContent(
            $response->getContent()
        );
    }
}
