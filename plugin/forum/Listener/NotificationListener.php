<?php

namespace Claroline\ForumBundle\Listener;

use Icap\NotificationBundle\Event\Notification\NotificationCreateDelegateViewEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * @DI\Service()
 */
class NotificationListener
{
    use ContainerAwareTrait;

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
            [
                'notification' => $notification,
                'status' => $notificationView->getStatus(),
                'systemName' => $event->getSystemName(),
            ]
        );
        $event->setResponseContent($content);
        $event->stopPropagation();
    }
}
