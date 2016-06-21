<?php

namespace Icap\BlogBundle\Listener;

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
     * @DI\Observe("create_notification_item_resource-icap_blog-post_create")
     * @DI\Observe("create_notification_item_resource-icap_blog-post_update")
     * @DI\Observe("create_notification_item_resource-icap_blog-post_publish")
     * @DI\Observe("create_notification_item_resource-icap_blog-comment_create")
     * @DI\Observe("create_notification_item_resource-icap_blog-comment_update")
     * @DI\Observe("create_notification_item_resource-icap_blog-comment_publish")
     * @DI\Observe("create_notification_item_resource-icap_blog-post-user_tagged")
     * @DI\Observe("create_notification_item_resource-icap_blog-comment-user_tagged")
     */
    public function onCreateNotificationItem(NotificationCreateDelegateViewEvent $event)
    {
        $notificationView = $event->getNotificationView();
        $notification = $notificationView->getNotification();
        $content = $this->templating->render(
            'IcapBlogBundle:Notification:notification_item.html.twig',
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
