<?php

namespace Icap\LessonBundle\Listener;

use Icap\NotificationBundle\Event\Notification\NotificationCreateDelegateViewEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class NotificationListener.
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

        $event->setPrimaryAction([
          'url' => 'icap_lesson_chapter',
          'parameters' => [
            'resourceId' => $notification->getDetails()['resource']['id'],
            'chapterId' => $notification->getDetails()['chapter']['chapter'],
          ],
        ]);

        $text = $this->translator->trans($notification->getActionKey(), ['%lesson%' => $notification->getDetails()['resource']['name'], '%chapter%' => $notification->getDetails()['chapter']['title']], 'notification');
        $event->setText($text);
    }
}
