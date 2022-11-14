<?php

namespace Icap\WikiBundle\Listener;

use Icap\NotificationBundle\Event\Notification\NotificationCreateDelegateViewEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * NotificationListener.
 */
class NotificationListener
{
    public function __construct(TranslatorInterface $translator, RouterInterface $router)
    {
        $this->translator = $translator;
        $this->router = $router;
    }

    public function onCreateNotificationItem(NotificationCreateDelegateViewEvent $event)
    {
        $notificationView = $event->getNotificationView();
        $notification = $notificationView->getNotification();

        //TODO: RESOURCE OPEN URL CHANGE
        $primaryAction = [
          'url' => 'claro_resource_show_short',
          'parameters' => [
            'id' => $notification->getDetails()['resource']['id'],
          ],
        ];

        $text = $this->translator->trans($notification->getActionKey(), ['%wiki%' => $notification->getDetails()['resource']['name'], '%section%' => $notification->getDetails()['section']['title']], 'notification');
        $event->setText($text);
        $event->setPrimaryAction($primaryAction);
    }
}
