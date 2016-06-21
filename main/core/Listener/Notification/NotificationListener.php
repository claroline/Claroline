<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener\Notification;

use Icap\NotificationBundle\Event\Notification\NotificationCreateDelegateViewEvent;
use Symfony\Component\DependencyInjection\ContainerAware;

class NotificationListener extends ContainerAware
{
    public function onCreateNotificationItem(NotificationCreateDelegateViewEvent $event)
    {
        $notificationView = $event->getNotificationView();
        $notification = $notificationView->getNotification();
        $content = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Notification:notification_item.html.twig',
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
