<?php

namespace Icap\PortfolioBundle\Listener;

use Claroline\CoreBundle\Event\Log\CreateFormResourceEvent;
use Icap\NotificationBundle\Event\Notification\NotificationCreateDelegateViewEvent;
use Symfony\Component\DependencyInjection\ContainerAware;

class NotificationListener extends ContainerAware
{
    public function onCreateNotificationItem(NotificationCreateDelegateViewEvent $event)
    {
        $notificationView = $event->getNotificationView();
        $notification = $notificationView->getNotification();
        $content = $this->container->get('templating')->render(
            'IcapPortfolioBundle:Notification:notification_item.html.twig',
            array(
                'notification'  => $notification,
                'status'        => $notificationView->getStatus(),
                'systemName'    => $event->getSystemName()
            )
        );

        $event->setResponseContent($content);
        $event->stopPropagation();
    }
}