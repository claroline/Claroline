<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\TagBundle\Listener;

use Claroline\CoreBundle\Event\ConfigureWidgetEvent;
use Claroline\CoreBundle\Event\DisplayWidgetEvent;
use Claroline\CoreBundle\Listener\NoHttpRequestException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service()
 */
class TagWidgetListener
{
    private $request;
    private $httpKernel;

    /**
     * @DI\InjectParams({
     *     "requestStack"   = @DI\Inject("request_stack"),
     *     "httpKernel"     = @DI\Inject("http_kernel")
     * })
     */
    public function __construct(
        RequestStack $requestStack,
        HttpKernelInterface $httpKernel
    ) {
        $this->request = $requestStack->getCurrentRequest();
        $this->httpKernel = $httpKernel;
    }

    /**
     * @DI\Observe("widget_claroline_tag_resources_widget")
     *
     * @param DisplayWidgetEvent $event
     *
     * @throws \Claroline\CoreBundle\Listener\NoHttpRequestException
     */
    public function onResourcesTagsWidgetDisplay(DisplayWidgetEvent $event)
    {
        if (!$this->request) {
            throw new NoHttpRequestException();
        }
        $widgetInstance = $event->getInstance();
        $params = array();
        $params['_controller'] = 'ClarolineTagBundle:Tag:resourcesTagsWidget';
        $params['widgetInstance'] = $widgetInstance->getId();
        $subRequest = $this->request->duplicate(
            array(),
            null,
            $params
        );
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setContent($response->getContent());
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("widget_claroline_tag_resources_widget_configuration")
     *
     * @param ConfigureWidgetEvent $event
     *
     * @throws \Claroline\CoreBundle\Listener\NoHttpRequestException
     */
    public function onResourcesTagsWidgetConfigure(ConfigureWidgetEvent $event)
    {
        if (!$this->request) {
            throw new NoHttpRequestException();
        }
        $widgetInstance = $event->getInstance();
        $params = array();
        $params['_controller'] = 'ClarolineTagBundle:Tag:resourcesTagsWidgetConfigureForm';
        $params['widgetInstance'] = $widgetInstance->getId();
        $subRequest = $this->request->duplicate(
            array(),
            null,
            $params
        );
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setContent($response->getContent());
    }
}
