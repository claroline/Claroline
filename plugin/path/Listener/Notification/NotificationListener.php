<?php

namespace Innova\PathBundle\Listener\Notification;

use Icap\NotificationBundle\Event\Notification\NotificationCreateDelegateViewEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerAware;

/**
 * @DI\Service()
 */
class NotificationListener extends ContainerAware
{
    /**
     * @DI\Observe("create_notification_item_resource-innova_path-step_unlock")
     *
     * @param NotificationCreateDelegateViewEvent $event
     */
    public function onCreateNotificationItem(NotificationCreateDelegateViewEvent $event)
    {
        $notificationView = $event->getNotificationView();

        $content = $this->container->get('templating')->render(
            'InnovaPathBundle:Notification:notification_item.html.twig', [
                'notification' => $notificationView->getNotification(),
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
     * @DI\Observe("create_notification_item_resource-innova_path-step_unlockdone")
     *
     * @param NotificationCreateDelegateViewEvent $event
     */
    public function onCreateNotificationUnlockDone(NotificationCreateDelegateViewEvent $event)
    {
        $notificationView = $event->getNotificationView();

        $content = $this->container->get('templating')->render(
            'InnovaPathBundle:Notification:unlockdone.html.twig', [
                'notification' => $notificationView->getNotification(),
                'status' => $notificationView->getStatus(),
                'systemName' => $event->getSystemName(),
            ]
        );

        $event->setResponseContent($content);
        $event->stopPropagation();
    }
}
