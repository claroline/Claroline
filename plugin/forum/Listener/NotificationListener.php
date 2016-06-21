<?php

namespace Claroline\ForumBundle\Listener;

use Icap\NotificationBundle\Event\Notification\NotificationCreateDelegateViewEvent;
use Symfony\Component\DependencyInjection\ContainerAware;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service()
 */
class NotificationListener extends ContainerAware
{
    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "templating" = @DI\Inject("templating")
     * })
     */
    public function __construct(
        $templating
    ) {
        $this->templating = $templating;
    }

    /**
     * @param NotificationUserParametersEvent $event
     *
     * @DI\Observe("create_notification_item_resource-claroline_forum-create_message")
     */
    public function onCreateNotificationItem(NotificationCreateDelegateViewEvent $event)
    {
        $notificationView = $event->getNotificationView();
        $notification = $notificationView->getNotification();
        $content = $this->templating->render(
            'ClarolineForumBundle:Notification:notification.html.twig',
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
