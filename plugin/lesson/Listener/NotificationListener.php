<?php

namespace Icap\LessonBundle\Listener;

use Icap\NotificationBundle\Event\Notification\NotificationCreateDelegateViewEvent;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class NotificationListener.
 */
class NotificationListener
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
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
