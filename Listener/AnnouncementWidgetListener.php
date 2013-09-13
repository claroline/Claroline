<?php

namespace Claroline\AnnouncementBundle\Listener;

use Claroline\CoreBundle\Event\DisplayWidgetEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service(scope="request")
 */
class AnnouncementWidgetListener
{
    private $request;
    private $httpKernel;

    /**
     * @DI\InjectParams({
     *     "request"    = @DI\Inject("request"),
     *     "httpKernel" = @DI\Inject("http_kernel"),
     * })
     */
    public function __construct(
        Request $request,
        HttpKernelInterface $httpKernel
    )
    {
        $this->request = $request;
        $this->httpKernel = $httpKernel;
    }

    /**
     * @DI\Observe("widget_claroline_announcement_widget_workspace")
     *
     * @param DisplayWidgetEvent $event
     */
    public function onWorkspaceDisplay(DisplayWidgetEvent $event)
    {
        $subRequest = $this->request->duplicate(
            array(),
            null,
            array(
                '_controller' => 'ClarolineAnnouncementBundle:Announcement:announcementsWorkspaceWidgetPager',
                'workspaceId' => $event->getWorkspace()->getId(),
                'page' => 1
            )
        );
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

        $event->setContent($response->getContent());
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("widget_claroline_announcement_widget_desktop")
     *
     * @param DisplayWidgetEvent $eve
     */
    public function onDesktopDisplay(DisplayWidgetEvent $event)
    {
        $subRequest = $this->request->duplicate(
            array(),
            null,
            array(
                '_controller' => 'ClarolineAnnouncementBundle:Announcement:announcementsDesktopWidgetPager',
                'page' => 1
            )
        );
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

        $event->setContent($response->getContent());
        $event->stopPropagation();
    }
}