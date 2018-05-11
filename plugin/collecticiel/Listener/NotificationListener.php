<?php

namespace Innova\CollecticielBundle\Listener;

use Icap\NotificationBundle\Event\Notification\NotificationCreateDelegateViewEvent;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class NotificationListener
{
    use ContainerAwareTrait;

    public function onCreateNotificationItem(NotificationCreateDelegateViewEvent $event)
    {
        $notificationView = $event->getNotificationView();
        $notification = $notificationView->getNotification();
        $content = $this->container->get('templating')->render(
            'InnovaCollecticielBundle:Notification:notification_item.html.twig',
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
