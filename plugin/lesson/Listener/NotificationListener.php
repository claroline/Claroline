<?php

namespace Icap\LessonBundle\Listener;

use Icap\NotificationBundle\Event\Notification\NotificationCreateDelegateViewEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class NotificationListener.
 *
 * @DI\Service
 */
class NotificationListener
{
    private $templating;

    /**
     * @DI\InjectParams({
     *     "translator" = @DI\Inject("translator"),
     *     "router"     = @DI\Inject("router")
     * })
     */
    public function __construct(TranslatorInterface $translator, RouterInterface $router)
    {
        $this->translator = $translator;
        $this->router = $router;
    }

    /**
     * @param NotificationCreateDelegateViewEvent $event
     * @DI\Observe("create_notification_item_resource-icap_lesson-chapter_create")
     * @DI\Observe("create_notification_item_resource-icap_lesson-chapter_update")
     * @DI\Observe("create_notification_item_resource-icap_lesson-user_tagged")
     */
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
