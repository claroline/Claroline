<?php

namespace Innova\PathBundle\EventListener\Notifications;

use Icap\NotificationBundle\Event\Notification\NotificationCreateDelegateViewEvent;
use Symfony\Component\DependencyInjection\ContainerAware;

class NotificationListener extends ContainerAware
{
    public function onCreateNotificationItem(NotificationCreateDelegateViewEvent $event)
    {
        $notificationView = $event->getNotificationView();
        $notification = $notificationView->getNotification();
        $content = $this->container->get('templating')->render(
            'InnovaPathBundle:Notification:notification_item.html.twig',
            [
                'notification' => $notification,
                'status' => $notificationView->getStatus(),
                'systemName' => $event->getSystemName(),
            ]
        );
        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    /**
     * Notification from Manager to Collaborator.
     *
     * @param NotificationCreateDelegateViewEvent $event
     */
    public function onCreateNotificationUnlockDone(NotificationCreateDelegateViewEvent $event)
    {
        $notificationView = $event->getNotificationView();
        $notification = $notificationView->getNotification();
        $content = $this->container->get('templating')->render(
            'InnovaPathBundle:Notification:unlockdone.html.twig',
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
