<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AnnouncementBundle\Listener\Widget;

use Claroline\CoreBundle\Event\ConfigureWidgetEvent;
use Claroline\CoreBundle\Event\DisplayWidgetEvent;
use Claroline\CoreBundle\Listener\NoHttpRequestException;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @DI\Service()
 */
class AnnouncementWidgetListener
{
    /** @var Request */
    private $request;

    /** @var HttpKernelInterface */
    private $httpKernel;

    /**
     * AnnouncementWidgetListener constructor.
     *
     * @DI\InjectParams({
     *     "requestStack" = @DI\Inject("request_stack"),
     *     "httpKernel"   = @DI\Inject("http_kernel")
     * })
     *
     * @param RequestStack        $requestStack,
     * @param HttpKernelInterface $httpKernel
     */
    public function __construct(
        RequestStack $requestStack,
        HttpKernelInterface $httpKernel
    ) {
        $this->request = $requestStack->getCurrentRequest();
        $this->httpKernel = $httpKernel;
    }

    /**
     * @DI\Observe("widget_claroline_announcement_widget")
     *
     * @param DisplayWidgetEvent $event
     *
     * @throws NoHttpRequestException
     */
    public function onDisplay(DisplayWidgetEvent $event)
    {
        if (!$this->request) {
            throw new NoHttpRequestException();
        }

        $subRequest = $this->request->duplicate([], null, [
            '_controller' => 'ClarolineAnnouncementBundle:Widget\AnnouncementWidget:announcementsWidget',
            'widgetInstance' => $event->getInstance()->getId(),
        ]);

        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setContent($response->getContent());

        $event->stopPropagation();
    }

    /**
     * @DI\Observe("widget_claroline_announcement_widget_configuration")
     *
     * @param ConfigureWidgetEvent $event
     *
     * @throws NoHttpRequestException
     */
    public function onConfig(ConfigureWidgetEvent $event)
    {
        if (!$this->request) {
            throw new NoHttpRequestException();
        }

        $subRequest = $this->request->duplicate([], null, [
            '_controller' => 'ClarolineAnnouncementBundle:Widget\AnnouncementWidget:configureForm',
            'widgetInstance' => $event->getInstance()->getId(),
        ]);

        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setContent($response->getContent());
    }
}
