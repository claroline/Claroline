<?php

namespace Icap\DropzoneBundle\Listener;

use Icap\NotificationBundle\Event\Notification\NotificationCreateDelegateViewEvent;
use Symfony\Component\DependencyInjection\ContainerAware;

class NotificationListener extends ContainerAware
{
    public function onCreateNotificationItem(NotificationCreateDelegateViewEvent $event)
    {
        $notificationView = $event->getNotificationView();
        $notification = $notificationView->getNotification();
        $content = $this->container->get('templating')->render(
            'IcapDropzoneBundle:Notification:notification_item.html.twig',
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
