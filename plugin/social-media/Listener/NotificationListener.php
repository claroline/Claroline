<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 * 
 * Date: 5/18/15
 */

namespace Icap\SocialmediaBundle\Listener;

use Icap\NotificationBundle\Event\Notification\NotificationCreateDelegateViewEvent;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class NotificationListener.
 *
 * @DI\Service
 */
class NotificationListener
{
    private $templating;

    /**
     * @DI\InjectParams({
     *     "templating" = @DI\Inject("templating")
     * })
     */
    public function __construct($templating)
    {
        $this->templating = $templating;
    }

    /**
     * @param NotificationCreateDelegateViewEvent $event
     * @DI\Observe("create_notification_item_resource-icap_socialmedia-comment_action")
     * @DI\Observe("create_notification_item_resource-icap_socialmedia-like_action")
     * @DI\Observe("create_notification_item_resource-icap_socialmedia-share_action")
     */
    public function onCreateNotificationItem(NotificationCreateDelegateViewEvent $event)
    {
        $notificationView = $event->getNotificationView();
        $notification = $notificationView->getNotification();
        $content = $this->templating->render(
            'IcapSocialmediaBundle:Notification:notification_item.html.twig',
            array(
                'notification' => $notification,
                'status' => $notificationView->getStatus(),
                'systemName' => $event->getSystemName(),
            )
        );

        $event->setResponseContent($content);
        $event->stopPropagation();
    }
}
