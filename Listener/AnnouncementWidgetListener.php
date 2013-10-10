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
     * @DI\Observe("widget_claroline_announcement_widget")
     *
     * @param DisplayWidgetEvent $event
     */
    public function onDisplay(DisplayWidgetEvent $event)
    {
        $widgetInstance = $event->getInstance();
        $workspace = $widgetInstance->getWorkspace();
        $params = array();
        $params['page'] = 1;

        if (is_null($workspace)) {
            $params['_controller'] = 'ClarolineAnnouncementBundle:Announcement:announcementsDesktopWidgetPager';
        }
        else {
            $params['_controller'] = 'ClarolineAnnouncementBundle:Announcement:announcementsWorkspaceWidgetPager';
            $params['workspaceId'] = $workspace->getId();
        }

        $subRequest = $this->request->duplicate(
            array(),
            null,
            $params
        );
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

        $event->setContent($response->getContent());
        $event->stopPropagation();
    }
}