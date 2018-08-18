<?php

namespace Claroline\VideoPlayerBundle\Listener;

use Claroline\CoreBundle\Event\InjectJavascriptEvent;
use Claroline\CoreBundle\Event\PluginOptionsEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bridge\Twig\TwigEngine;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @DI\Service
 */
class PluginListener
{
    /** @var TwigEngine */
    private $templating;

    /** @var HttpKernelInterface */
    private $httpKernel;

    /** @var RequestStack */
    private $request;

    /**
     * VideoPlayerListener constructor.
     *
     * @DI\InjectParams({
     *     "templating"      = @DI\Inject("templating"),
     *     "httpKernel"      = @DI\Inject("http_kernel"),
     *     "requestStack"    = @DI\Inject("request_stack")
     * })
     *
     * @param TwigEngine          $templating
     * @param HttpKernelInterface $httpKernel
     * @param RequestStack        $requestStack
     */
    public function __construct(
        TwigEngine $templating,
        HttpKernelInterface $httpKernel,
        RequestStack $requestStack
    ) {
        $this->templating = $templating;
        $this->httpKernel = $httpKernel;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * @DI\Observe("plugin_options_videoplayerbundle")
     *
     * @param PluginOptionsEvent $event
     */
    public function onConfigure(PluginOptionsEvent $event)
    {
        $params = ['_controller' => 'ClarolineVideoPlayerBundle:VideoPlayer:AdminOpen'];
        $subRequest = $this->request->duplicate([], null, $params);
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("inject_javascript_layout")
     *
     * @param InjectJavascriptEvent $event
     */
    public function onInjectJs(InjectJavascriptEvent $event)
    {
        $event->addContent(
            $this->templating->render('ClarolineVideoPlayerBundle::scripts.html.twig', [])
        );
    }
}
