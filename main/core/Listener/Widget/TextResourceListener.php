<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener\Widget;

use Claroline\CoreBundle\Event\ConfigureWidgetEvent;
use Claroline\CoreBundle\Event\DisplayWidgetEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @DI\Service()
 */
class TextResourceListener
{
    private $request;
    private $httpKernel;

    /**
     * @DI\InjectParams({
     *     "requestStack"        = @DI\Inject("request_stack")
     * })
     */
    public function __construct(
        HttpKernelInterface $httpKernel,
        RequestStack $requestStack
    ) {
        $this->httpKernel = $httpKernel;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * @DI\Observe("widget_resource_text")
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
        $params['_controller'] = 'ClarolineCoreBundle:Widget\ResourcesWidget:displayResourceTextWidget';
        $params['widgetInstance'] = $widgetInstance->getId();
        $subRequest = $this->request->duplicate([], null, $params);
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setContent($response->getContent());
    }

    /**
     * @DI\Observe("widget_resource_text_configuration")
     */
    public function onConfig(ConfigureWidgetEvent $event)
    {
        if (!$this->request) {
            throw new NoHttpRequestException();
        }

        $widgetInstance = $event->getInstance();
        $params = [];
        $params['_controller'] = 'ClarolineCoreBundle:Widget\ResourcesWidget:resourceTextConfigureForm';
        $params['widgetInstance'] = $widgetInstance->getId();
        $subRequest = $this->request->duplicate([], null, $params);
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setContent($response->getContent());
    }
}
