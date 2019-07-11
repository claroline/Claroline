<?php

namespace Icap\WikiBundle\Listener;

use Icap\NotificationBundle\Event\Notification\NotificationCreateDelegateViewEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * NotificationListener.
 *
 * @DI\Service
 */
class NotificationListener
{
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
     * @DI\Observe("create_notification_item_resource-icap_wiki-section_create")
     * @DI\Observe("create_notification_item_resource-icap_wiki-contribution_create")
     * @DI\Observe("create_notification_item_resource-icap_wiki-user_tagged")
     *
     * @param NotificationCreateDelegateViewEvent $event
     */
    public function onCreateNotificationItem(NotificationCreateDelegateViewEvent $event)
    {
        $notificationView = $event->getNotificationView();
        $notification = $notificationView->getNotification();

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
