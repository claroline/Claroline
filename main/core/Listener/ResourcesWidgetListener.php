<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace  Claroline\CoreBundle\Listener;

use Claroline\CoreBundle\Event\ConfigureWidgetEvent;
use Claroline\CoreBundle\Event\DisplayWidgetEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @DI\Service
 */
class ResourcesWidgetListener
{
    private $httpKernel;
    private $request;

    /**
     * @DI\InjectParams({
     *     "requestStack" = @DI\Inject("request_stack"),
     *     "httpKernel"   = @DI\Inject("http_kernel")
     * })
     */
    public function __construct(HttpKernelInterface $httpKernel, RequestStack $requestStack)
    {
        $this->httpKernel = $httpKernel;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * @DI\Observe("widget_resources_widget")
     *
     * @param DisplayWidgetEvent $event
     */
    public function onDisplay(DisplayWidgetEvent $event)
    {
        if (!$this->request) {
            throw new NoHttpRequestException();
        }
        $widgetInstance = $event->getInstance();
        $params = [];
        $params['_controller'] = 'ClarolineCoreBundle:Widget\ResourcesWidget:resourcesWidget';
        $params['widgetInstance'] = $widgetInstance->getId();
        $subRequest = $this->request->duplicate([], null, $params);
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setContent($response->getContent());
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("widget_resources_widget_configuration")
     */
    public function onConfig(ConfigureWidgetEvent $event)
    {
        if (!$this->request) {
            throw new NoHttpRequestException();
        }
        $widgetInstance = $event->getInstance();
        $params = [];
        $params['_controller'] = 'ClarolineCoreBundle:Widget\ResourcesWidget:resourcesWidgetConfigureForm';
        $params['widgetInstance'] = $widgetInstance->getId();
        $subRequest = $this->request->duplicate([], null, $params);
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setContent($response->getContent());
    }
}
