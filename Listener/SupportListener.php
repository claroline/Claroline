<?php

namespace FormaLibre\SupportBundle\Listener;

use Claroline\CoreBundle\Event\DisplayToolEvent;
use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
use Claroline\CoreBundle\Event\PluginOptionsEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @DI\Service
 */
class SupportListener
{
    private $httpKernel;
    private $request;

    /**
     * @DI\InjectParams({
     *     "httpKernel"   = @DI\Inject("http_kernel"),
     *     "requestStack"  = @DI\Inject("request_stack")
     * })
     */
    public function __construct(
        HttpKernelInterface $httpKernel,
        RequestStack $requestStack
    )
    {
        $this->httpKernel = $httpKernel;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * @DI\Observe("administration_tool_formalibre_support_management_tool")
     *
     * @param DisplayToolEvent $event
     */
    public function onAdministrationToolOpen(OpenAdministrationToolEvent $event)
    {
        $params = array();
        $params['_controller'] = 'FormaLibreSupportBundle:AdminSupport:adminSupportIndex';
        $subRequest = $this->request->duplicate(array(), null, $params);
        $response = $this->httpKernel
            ->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("open_tool_desktop_formalibre_support_tool")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayDesktopResourceManager(DisplayToolEvent $event)
    {
        $params = array();
        $params['_controller'] = 'FormaLibreSupportBundle:Support:supportIndex';
        $subRequest = $this->request->duplicate(array(), null, $params);
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setContent($response->getContent());
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("plugin_options_supportbundle")
     *
     * @param DisplayToolEvent $event
     */
    public function onPluginOptionsOpen(PluginOptionsEvent $event)
    {
        $params = array();
        $params['_controller'] = 'FormaLibreSupportBundle:AdminSupport:pluginConfigureForm';
        $subRequest = $this->request->duplicate(array(), null, $params);
        $response = $this->httpKernel
            ->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setResponse($response);
        $event->stopPropagation();
    }
}
